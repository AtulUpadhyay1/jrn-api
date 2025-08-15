<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $casts = [
        'resume_parsed' => 'array',
    ];
}
