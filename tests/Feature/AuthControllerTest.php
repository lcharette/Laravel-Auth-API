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

        $response = $this->post('/api/login', [
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

    public function testLoginWithUser(): void
    {
        $user = UserFactory::new()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/login', [
                'email'    => $user->email,
                'password' => 'password',
            ]);

        $response
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);

        // We're still authenticated, as we already were
        $this->assertAuthenticated('api');
    }

    public function testLoginWithWrongPassword(): void
    {
        $user = UserFactory::new()->create();

        $response = $this->post('/api/login', [
            'email'    => $user->email,
            'password' => 'fooBar',
        ]);

        $response
            ->assertStatus(403)
            ->assertJson(['error' => 'Unauthorized']);

        // Still a guest
        $this->assertGuest('api');
    }

    public function testLogout(): void
    {
        $user = UserFactory::new()->create();

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->post('/api/logout');

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);

        $this->assertGuest('api');
    }

    /**
     * @depends testLogout
     */
    public function testLogoutWithNoAccount(): void
    {
        $response = $this->post('/api/logout');

        $response
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);

        // Still a guest
        $this->assertGuest('api');
    }

    public function testRefresh(): void
    {
        $user = UserFactory::new()->create();

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->post('/api/refresh');

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
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->get('/api/user');

        $expectedStructure = [
            'status',
            'data',
        ];

        $response
            ->assertStatus(200)
            ->assertJsonStructure($expectedStructure); // TODO : Test we get the correct info
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

    public function testRegister(): void
    {
        $response = $this->post('/api/register', [
            'username'              => 'foo',
            'email'                 => 'test@test.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Still a guest
        $this->assertGuest('api');
    }

    public function testRegisterWithNoData(): void
    {
        $response = $this->post('/api/register');
        $response->assertStatus(400);
    }

    public function testRegisterWithMissingUsername(): void
    {
        $response = $this->post('/api/register', [
            // 'username' => 'foo',
            'email'                 => 'test@test.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'errors' => [
                    'username' => [
                        'The username field is required.'
                    ]
                ]
            ]);
    }

    public function testRegisterWithMissingEmail(): void
    {
        $response = $this->post('/api/register', [
            'username' => 'foo',
            // 'email'    => 'test@test.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'errors' => [
                    'email' => [
                        'The email field is required.'
                    ]
                ]
            ]);
    }

    public function testRegisterWithMissingPasswordConfirmatiton(): void
    {
        $response = $this->post('/api/register', [
            'username' => 'foo',
            'email'    => 'test@test.com',
            'password' => 'password',
        ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'errors' => [
                    'password' => [
                        'The password confirmation does not match.'
                    ]
                ]
            ]);
    }

    public function testRegisterWithUser(): void
    {
        $user = UserFactory::new()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/register');

        $response
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);

        // We're still authenticated, as we already were
        $this->assertAuthenticated('api');
    }
}
