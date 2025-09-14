<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinkedInProfileAi extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'profile',
        'ai_report',
        'snapshot_id',
        'api_status',
        'status'
    ];

    protected $casts = [
        // 'profile' => 'array',
        // 'ai_report' => 'array',
        // 'snapshot_id' => 'array',
        'api_status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
