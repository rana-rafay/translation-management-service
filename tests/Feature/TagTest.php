<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user  = User::factory()->create();
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
    }

    private function authHeader(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_can_list_all_tags(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->withHeaders($this->authHeader())
                         ->getJson('/api/tags');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_can_create_tag(): void
    {
        $response = $this->withHeaders($this->authHeader())
                         ->postJson('/api/tags', [
                             'name' => 'mobile',
                         ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Tag created successfully',
                 ]);

        $this->assertDatabaseHas('tags', ['name' => 'mobile']);
    }

    public function test_cannot_create_duplicate_tag(): void
    {
        Tag::factory()->create(['name' => 'mobile']);

        $response = $this->withHeaders($this->authHeader())
                         ->postJson('/api/tags', [
                             'name' => 'mobile',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_can_view_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->withHeaders($this->authHeader())
                         ->getJson('/api/tags/' . $tag->id);

        $response->assertStatus(200)
                 ->assertJson(['id' => $tag->id]);
    }

    public function test_can_update_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'mobile']);

        $response = $this->withHeaders($this->authHeader())
                         ->putJson('/api/tags/' . $tag->id, [
                             'name' => 'ios',
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Tag updated successfully']);

        $this->assertDatabaseHas('tags', ['name' => 'ios']);
    }

    public function test_can_delete_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->withHeaders($this->authHeader())
                         ->deleteJson('/api/tags/' . $tag->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Tag deleted successfully']);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_unauthenticated_user_cannot_access_tags(): void
    {
        $response = $this->getJson('/api/tags');

        $response->assertStatus(401);
    }
}