<?php

namespace App\Services;

use OpenAI\Client as OpenAIClient;

class CoverLetterService
{
    public function __construct(private OpenAIClient $openai) {}

    public function generate(array $cfg): array
    {
        // Convert words → tokens (rough heuristic)
        $maxTokens = (int) ceil(min(4000, max(100, ($cfg['max_words'] ?? 1500) * 1.35)));

        $system = $this->buildSystemPrompt($cfg);
        $user   = $this->buildUserPrompt($cfg);

        $params = [
            'model'       => $cfg['model'] ?? 'gpt-4o-mini',
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
            'temperature' => (float) $cfg['temperature'],
            'max_tokens'  => $maxTokens,
        ];

        // Optional advanced controls
        foreach (['presence_penalty','frequency_penalty','top_p','seed','stop'] as $k) {
            if (isset($cfg[$k])) { $params[$k] = $cfg[$k]; }
        }

        // Call OpenAI (Chat Completions)
        $resp = $this->openai->chat()->create($params); // usage pattern per client docs. :contentReference[oaicite:1]{index=1}
        $text = trim($resp->choices[0]->message->content ?? '');

        return [
            'content' => $text,
            'usage'   => [
                'prompt_tokens'     => $resp->usage->promptTokens ?? null,
                'completion_tokens' => $resp->usage->completionTokens ?? null,
                'total_tokens'      => $resp->usage->totalTokens ?? null,
                'model'             => $params['model'],
            ],
        ];
    }

    private function buildSystemPrompt(array $c): string
    {
        $tone = $c['tone'] ?? 'polite';
        $style = $c['style'] ?? 'plain';
        $persp = $c['perspective'] ?? 'first_person';
        $lang = $c['language'] ?? 'en-US';
        $region = $c['region_spelling'] ?? 'US';

        return <<<SYS
            You are an expert cover-letter writer.
            Write in a {$tone}, professional tone, {$persp} perspective.
            Language: {$lang}. Spelling/locale preference: {$region}.
            Keep the letter ATS-friendly (no emojis, no images), avoid fluff, and
            stay under the requested word limit. If bullets are requested, keep them concise.
            Return only the letter content in {$style} with no extra commentary.
            SYS;
    }

    private function buildUserPrompt(array $c): string
    {
        $name     = $c['name'];
        $company  = $c['company'] ?? null;
        $role     = $c['role'] ?? null;
        $skills   = $c['skills'] ?? [];
        $exp      = $c['experience_years'] ?? null;
        $maxWords = $c['max_words'] ?? 1500;
        $useBul   = !empty($c['use_bullets']) ? 'yes' : 'no';
        $closing  = $c['closing'] ?? 'Sincerely';
        $signName = $c['sign_off_name'] ?? $name;

        $sections = $c['structure'] ?? [
            'opener','fit','key_achievements','why_company','closing'
        ];

        $jd = trim($c['description']);

        $skillsTxt = $skills ? ('Skills to highlight: ' . implode(', ', $skills) . ".\n") : '';
        $expTxt    = $exp !== null ? "Years of experience: {$exp}.\n" : '';

        $secTxt = implode(', ', $sections);

        return <<<USER
            Candidate Name: {$name}
            Target Role: {$role}
            Company: {$company}
            {$skillsTxt}{$expTxt}
            Sections to include (in order): {$secTxt}
            Use short bullet points if helpful: {$useBul}
            Maximum words: {$maxWords}
            Preferred closing: {$closing}
            Sign-off name: {$signName}

            Job Description:
            ----------------
            {$jd}
            USER;
    }
}

