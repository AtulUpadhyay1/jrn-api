<?php

namespace App\Services;

use GuzzleHttp\Client as HttpClient;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Str;
use OpenAI;

class ResumeParseService
{
    protected HttpClient $http;
    protected \OpenAI\Client $openai;
    protected string $model;

    public function __construct()
    {
        $this->http   = new HttpClient(['timeout' => 25, 'verify' => true]);
        $this->openai = OpenAI::client(config('services.openai.api_key'));
        $this->model  = config('services.openai.model');
    }

    /**
     * Main entry: fetch PDF by URL, extract text, parse with LLM, return JSON.
     */
    public function parseFromUrl(string $url, ?string $jobDescription = null): array
    {
        $pdfPath   = $this->downloadPdfToTemp($url);
        $rawText   = $this->extractText($pdfPath);
        $structured = $this->llmToSchema($rawText, $jobDescription);

        return [
            'status'     => 'ok',
            'source_url' => $url,
            // Optional small preview for debugging; remove if not needed
            'text_preview' => Str::limit($rawText, 1200),
            'structured' => $structured,
        ];
    }

    /**
     * Download PDF to a temp file with basic content checks.
     */
    protected function downloadPdfToTemp(string $url): string
    {
        // HEAD request (best-effort) to validate content-type/size
        try {
            $head = $this->http->request('HEAD', $url);
            $ct   = strtolower($head->getHeaderLine('Content-Type'));
            $len  = (int) $head->getHeaderLine('Content-Length');

            // Allow servers that misreport content-type; we still check extension & content later
            if ($len > 25 * 1024 * 1024) { // 25MB limit
                throw new \RuntimeException('PDF too large.');
            }
        } catch (\Throwable $e) {
            // continue; not all servers allow HEAD
        }

        $tmp = tempnam(sys_get_temp_dir(), 'resume_') . '.pdf';
        $resp = $this->http->get($url, ['stream' => true]);

        // If server sends type, ensure it's likely a PDF
        $ctDl = strtolower($resp->getHeaderLine('Content-Type'));
        if ($ctDl && !Str::contains($ctDl, 'pdf')) {
            // still allow download; some CDNs use octet-stream; just ensure extension .pdf
            if (!Str::endsWith(Str::of($url)->lower(), '.pdf')) {
                throw new \RuntimeException('URL does not look like a PDF.');
            }
        }

        $body = $resp->getBody();
        $fh = fopen($tmp, 'w');
        while (!$body->eof()) {
            fwrite($fh, $body->read(8192));
        }
        fclose($fh);

        // Minimal magic header check (%PDF)
        $fh = fopen($tmp, 'r');
        $sig = fread($fh, 4);
        fclose($fh);
        if ($sig !== '%PDF') {
            throw new \RuntimeException('Downloaded file is not a PDF.');
        }

        return $tmp;
    }

    /**
     * Extract raw text from PDF using poppler's pdftotext via Spatie.
     */
    protected function extractText(string $pdfPath): string
    {
        $text = trim(Pdf::getText($pdfPath));

        if ($text === '') {
            throw new \RuntimeException('Empty text extracted from PDF.');
        }
        // simple cleanup
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n{2,}/", "\n\n", $text);
        return $text;
    }

    /**
     * Ask ChatGPT to convert resume text → strict JSON schema.
     */
    protected function llmToSchema(string $resumeText, ?string $jobDescription = null): array
    {
        $system = <<<SYS
        You are a resume-to-JSON structuring assistant for ATS ingestion.
        Return ONLY valid JSON that matches this schema exactly. No prose and no markdown.

        Schema:
        {
        "user_details": {
            "full_name": "string",
            "email": "string|null",
            "phone": "string|null",
            "location": "string|null",
            "headline": "string|null",
            "summary": "string|null",
            "linkedin": "string|null",
            "github": "string|null",
            "website": "string|null"
        },
        "experiences": [
            {
            "company": "string",
            "role": "string",
            "location": "string|null",
            "start_date": "YYYY|YYYY-MM|YYYY-MM-DD|null",
            "end_date": "YYYY|YYYY-MM|YYYY-MM-DD|null|\"Present\"",
            "description": "string|null",
            "achievements": ["string", "..."]
            }
        ],
        "education": [
            {
            "institution": "string",
            "degree": "string|null",
            "field": "string|null",
            "start_date": "YYYY|YYYY-MM|null",
            "end_date": "YYYY|YYYY-MM|null",
            "grade": "string|null"
            }
        ],
        "skills": [
            {"name": "string", "category": "technical|soft|tool|domain|null", "proficiency": "beginner|intermediate|advanced|expert|null", "years": "number|null"}
        ],
        "projects": [
            {"name": "string", "role": "string|null", "description": "string|null", "tech_stack": ["string","..."], "start_date": "YYYY|YYYY-MM|null", "end_date": "YYYY|YYYY-MM|null", "link": "string|null"}
        ],
        "certifications": [
            {"name": "string", "issuer": "string|null", "year": "YYYY|null", "credential_id": "string|null", "url": "string|null"}
        ],
        "languages": [
            {"name": "string", "proficiency": "native|fluent|professional|limited|basic|null"}
        ]
        }
        Unknown fields MUST be null or empty arrays. Deduplicate similar entries.
        SYS;

                $user = <<<USR
        Resume Text:
        ----------------
        {$resumeText}
        ----------------

        Job Description (optional, may be empty):
        ----------------
        {$jobDescription}
        ----------------

        Return ONLY the JSON object above.
        USR;

        $resp = $this->openai->chat()->create([
            'model' => $this->model,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
            'temperature' => 0.1,
        ]);

        $json = $resp->choices[0]->message->content ?? '{}';
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Model returned invalid JSON.');
        }

        // Minimal normalization so you get consistent keys
        $data['user_details'] = $data['user_details'] ?? [];
        $data['experiences']  = array_values($data['experiences']  ?? []);
        $data['education']    = array_values($data['education']    ?? []);
        $data['skills']       = array_values($data['skills']       ?? []);
        $data['projects']     = array_values($data['projects']     ?? []);
        $data['certifications'] = array_values($data['certifications'] ?? []);
        $data['languages']    = array_values($data['languages']    ?? []);

        return $data;
    }
}
