<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QboToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'realm_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'id_token',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
