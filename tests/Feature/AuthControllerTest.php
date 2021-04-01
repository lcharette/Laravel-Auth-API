<?php
/**
 * Laravel Auth API
 *
 * @author    Louis Charette
 * @copyright Copyright (c) 2021 Louis Charette
 * @link      https://github.com/lcharette/laravel-auth-api
 * @license   https://github.com/lcharette/laravel-auth-api/blob/master/LICENSE.md (MIT License)
 */

namespace Lcharette\AuthApi\Tests\Feature;

use Lcharette\AuthApi\Tests\Feature\Models\UserFactory;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    public function testLogin(): void
    {
        $user = UserFactory::new()->create();

        $response = $this->actingAs($user)->post('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $expectedStructure = [
            'access_token',
            'token_type',
            'expires_in'
        ];

        $response
            ->assertStatus(200)
            ->assertJsonStructure($expectedStructure);

        $this->assertAuthenticated('api');
    }

    public function testLoginWithWrongPassword(): void
    {
        $user = UserFactory::new()->create();

        $response = $this->actingAs($user)->post('/api/login', [
            'email'    => $user->email,
            'password' => 'fooBar',
        ]);

        $response
            ->assertStatus(403)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function testLogout(): void
    {
        $user = UserFactory::new()->create();

        $token = JWTAuth::fromUser($user);

        $response = $this->actingAs($user)->post('/api/logout?token=' . $token);

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);

        $this->assertGuest('api');
    }

    public function testRefresh(): void
    {
        $user = UserFactory::new()->create();

        $token = JWTAuth::fromUser($user);

        $response = $this->actingAs($user)->post('/api/refresh?token=' . $token);

        $expectedStructure = [
            'access_token',
            'token_type',
            'expires_in'
        ];

        $response
            ->assertStatus(200)
            ->assertJsonStructure($expectedStructure);
    }

    public function testRefreshWithNoAccount(): void
    {
        $response = $this->post('/api/refresh');

        $response
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function testUser(): void
    {
        $user = UserFactory::new()->create();

        $response = $this->actingAs($user)->get('/api/user');

        $expectedStructure = [
            'status',
            'data',
        ];

        $response
            ->assertStatus(200)
            ->assertJsonStructure($expectedStructure);
    }

    /**
     * @depends testUser
     */
    public function testUserWithNoAccount(): void
    {
        $response = $this->get('/api/user');

        $response
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }
}
