<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinkedInProfileAi extends Model
{
    use SoftDeletes;

    protected $cast = [
        'profile' => 'array',
        'ai_report' => 'array',
    ];
}
