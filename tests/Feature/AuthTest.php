<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_success(): void
    {
        $this->post('/api/auth/register', [
            'name' => 'syahrizal',
            'email' => 'syahrizal@example.com',
            'password' => 'password123'
        ])->assertStatus(201)
            ->assertJson([
                "data" => [
                    'name' => 'syahrizal',
                    'email' => 'syahrizal@example.com',
                    'status' => true,
                    'message' => 'User Created Successfully'
                ]
            ]);
    }

    public function test_register_failed_validation_errors(): void
    {
        $this->post('/api/auth/register', [
            'name' => '',
            'email' => '',
            'password' => '',
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'name' => ['The name field is required.'],
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
                ],
                'status' => false,
                'message' => 'validation error',
            ]);
    }

    public function test_register_email_already_exists(): void
    {
        $this->test_register_success();
        $this->post('/api/auth/register', [
            'name' => 'syahrizal',
            'email' => 'syahrizal@example.com',
            'password' => 'password123'
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'email' => ['The email has already been taken.']
                ],
                'status' => false,
                'message' => 'validation error',
            ]);
    }

    public function test_login_success(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'test',
                    'email' => 'test@example.com',
                    'status' => true,
                    'message' => 'Logged In Successfully'
                ]
            ])->assertJsonStructure([
                'data' => [
                    'name',
                    'email',
                    'status',
                    'message',
                    'token',
                ],
            ]);
    }

    public function test_login_failed_user_not_found(): void
    {
        $this->post('/api/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong',
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        "username or password wrong"
                    ]
                ]
            ]);
    }

    public function test_login_failed_email_wrong(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        "username or password wrong"
                    ]
                ]
            ]);
    }

    public function test_login_failed_password_wrong(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        "username or password wrong"
                    ]
                ]
            ]);
    }
}
