<?php
/**
 * Laravel Auth API
 *
 * @author    Louis Charette
 * @copyright Copyright (c) 2021 Louis Charette
 * @link      https://github.com/lcharette/laravel-auth-api
 * @license   https://github.com/lcharette/laravel-auth-api/blob/master/LICENSE.md (MIT License)
 */

namespace Lcharette\AuthApi\Tests\Feature\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Lcharette\AuthApi\Auth\isJWTSubject;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property \Datetime $created_at
 * @property \Datetime $updated_at
 */
class User extends Authenticatable implements JWTSubject
{
    use isJWTSubject;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];
}
