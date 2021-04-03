<?php
/**
 * Laravel Auth API
 *
 * @author    Louis Charette
 * @copyright Copyright (c) 2021 Louis Charette
 * @link      https://github.com/lcharette/laravel-auth-api
 * @license   https://github.com/lcharette/laravel-auth-api/blob/master/LICENSE.md (MIT License)
 */

namespace Lcharette\AuthApi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'username'  => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:4|confirmed',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 400);
        }

        $model = $this->getUserModel();
        $user = new $model();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(): JsonResponse
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'data'   => $user
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the user model from the auth provider config.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable;
     */
    protected function getUserModel()
    {
        return config('auth.providers.users.model');
    }
}
