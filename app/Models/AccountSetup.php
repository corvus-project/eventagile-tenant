<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'config',
        'action',
    ];

    protected $casts = [
        'config' => 'array',
    ];
}
