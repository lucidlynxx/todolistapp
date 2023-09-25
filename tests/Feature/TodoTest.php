<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\TodoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_success(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/todos', [
                'title' => 'test'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'test',
                    'has_completed' => false,
                    'status' => true
                ]
            ]);
    }

    public function test_store_failed_validation_errors(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/todos', [
                'title' => ''
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'title' => [
                        'The title field is required.'
                    ]
                ]
            ]);
    }

    public function test_store_failed_title_already_exists(): void
    {
        $this->test_store_success();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/todos', [
                'title' => 'test'
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'title' => [
                        'The title has already been taken.'
                    ]
                ]
            ]);
    }

    public function test_index_success(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->post('/api/todos', [
                'title' => 'test'
            ]);

        $this->actingAs($user, 'sanctum')
            ->get('/api/todos')->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'title' => 'test',
                        'has_completed' => false,
                        'status' => true
                    ]
                ]
            ]);
    }

    // public function test_index_failed_not_found(): void
    // {
    //     $user = User::factory()->create();

    //     $this->actingAs($user, 'sanctum')
    //         ->get('/api/todos')->assertStatus(404)
    //         ->assertJson([
    //             'errors' => [
    //                 'message' => [
    //                     'Not Found'
    //                 ]
    //             ]
    //         ]);
    // }

    public function test_show_success(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->post('/api/todos/', [
                'title' => 'test'
            ]);

        //! angka 4 pada parameter adalah id dari data todolist yang dibuat dari kode di atas
        $this->actingAs($user, 'sanctum')
            ->get('/api/todos/' . 4)->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'test',
                    'has_completed' => false,
                    'status' => true
                ]
            ]);
    }

    public function test_show_failed_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->get('/api/todos/' . 4)->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Not Found'
                    ]
                ]
            ]);
    }

    public function test_update_success(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->post('/api/todos/', [
                'title' => 'test'
            ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'test',
                    'has_completed' => false,
                    'status' => true
                ]
            ]);

        //! angka 5 pada parameter adalah id dari data todolist yang dibuat dari kode di atas
        $this->actingAs($user, 'sanctum')
            ->put('/api/todos/' . 5, [
                'has_completed' => true
            ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'test',
                    'has_completed' => true,
                    'status' => true
                ]
            ]);
    }

    public function test_update_failed_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->post('/api/todos/', [
                'title' => 'test'
            ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'test',
                    'has_completed' => false,
                    'status' => true
                ]
            ]);

        //! angka 5 pada parameter adalah id dari data todolist yang dibuat dari kode di atas
        $this->actingAs($user, 'sanctum')
            ->put('/api/todos/' . 5, [
                'has_completed' => true
            ])->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Not Found'
                    ]
                ]
            ]);
    }

    public function test_update_failed_validation_errors(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->post('/api/todos/', [
                'title' => 'test'
            ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'test',
                    'has_completed' => false,
                    'status' => true
                ]
            ]);

        //! angka 7 pada parameter adalah id dari data todolist yang dibuat dari kode di atas
        $this->actingAs($user, 'sanctum')
            ->put('/api/todos/' . 7)->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "has_completed" => [
                        "The has completed field is required."
                    ]
                ],
                "status" => false,
                "message" => "validation error"
            ]);
    }

    public function test_destroy_success(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->post('/api/todos/', [
                'title' => 'test'
            ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'test',
                    'has_completed' => false,
                    'status' => true
                ]
            ]);

        //! angka 8 pada parameter adalah id dari data todolist yang dibuat dari kode di atas
        $this->actingAs($user, 'sanctum')
            ->delete('/api/todos/' . 8)->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'data deleted'
            ]);
    }

    public function test_destroy_failed_not_found(): void
    {
        $user = User::factory()->create();

        //! angka 7 pada parameter adalah id dari data todolist yang dibuat dari kode di atas
        $this->actingAs($user, 'sanctum')
            ->delete('/api/todos/' . 8)->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Not Found'
                    ]
                ]
            ]);
    }
}
