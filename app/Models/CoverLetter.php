<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoverLetter extends Model
{
    use SoftDeletes;

    protected $casts = [
        'skills' => 'array',
        'structure' => 'array',
    ];

}
