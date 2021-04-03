<?php
/**
 * Laravel Auth API
 *
 * @author    Louis Charette
 * @copyright Copyright (c) 2021 Louis Charette
 * @link      https://github.com/lcharette/laravel-auth-api
 * @license   https://github.com/lcharette/laravel-auth-api/blob/master/LICENSE.md (MIT License)
 */

namespace Lcharette\AuthApi\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class RequireGuest
{
    /**
     * The JWT Authenticator.
     *
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $auth;

    /**
     * @param \Tymon\JWTAuth\JWTAuth $auth
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    // Override handle method
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            if ($this->auth->parseToken()->authenticate()) {
                return response()->json(['error'=>'Unauthorized'], 401);
            }
        } catch (JWTException $e) {
            // Continue on exception
        }

        return $next($request);
    }
}
