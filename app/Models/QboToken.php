<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $realm_id
 * @property string $access_token
 * @property string $refresh_token
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property string|null $id_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken whereIdToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken whereRealmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QboToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
