<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationTest extends TestCase
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

    public function test_can_list_translations(): void
    {
        Translation::factory()->count(5)->create();

        $response = $this->withHeaders($this->authHeader())
                         ->getJson('/api/translations');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'meta',
                     'links',
                 ]);
    }

    public function test_can_create_translation(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->withHeaders($this->authHeader())
                         ->postJson('/api/translations', [
                             'locale' => 'en',
                             'key'    => 'welcome_message',
                             'value'  => 'Welcome!',
                             'tags'   => [$tag->id],
                         ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Translation created successfully',
                 ]);

        $this->assertDatabaseHas('translations', [
            'locale' => 'en',
            'key'    => 'welcome_message',
        ]);
    }

    public function test_cannot_create_duplicate_translation_key_for_same_locale(): void
    {
        Translation::factory()->create([
            'locale' => 'en',
            'key'    => 'welcome_message',
        ]);

        $response = $this->withHeaders($this->authHeader())
                         ->postJson('/api/translations', [
                             'locale' => 'en',
                             'key'    => 'welcome_message',
                             'value'  => 'Welcome again!',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['key']);
    }

    public function test_can_create_same_key_for_different_locale(): void
    {
        Translation::factory()->create([
            'locale' => 'en',
            'key'    => 'welcome_message',
        ]);

        $response = $this->withHeaders($this->authHeader())
                         ->postJson('/api/translations', [
                             'locale' => 'fr',
                             'key'    => 'welcome_message',
                             'value'  => 'Bienvenue!',
                         ]);

        $response->assertStatus(201);
    }

    public function test_can_view_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->withHeaders($this->authHeader())
                         ->getJson('/api/translations/' . $translation->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'locale',
                         'key',
                         'value',
                         'tags',
                     ],
                 ]);
    }

    public function test_can_update_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->withHeaders($this->authHeader())
                         ->putJson('/api/translations/' . $translation->id, [
                             'value' => 'Updated value!',
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Translation updated successfully',
                 ]);
    }

    public function test_can_delete_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->withHeaders($this->authHeader())
                         ->deleteJson('/api/translations/' . $translation->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Translation deleted successfully',
                 ]);

        $this->assertDatabaseMissing('translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_can_search_by_locale(): void
    {
        Translation::factory()->create(['locale' => 'en']);
        Translation::factory()->create(['locale' => 'fr']);

        $response = $this->withHeaders($this->authHeader())
                         ->getJson('/api/translations?locale=en');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertTrue(collect($data)->every(fn($t) => $t['locale'] === 'en'));
    }

    public function test_can_search_by_key(): void
    {
        Translation::factory()->create(['key' => 'welcome_message']);
        Translation::factory()->create(['key' => 'logout_button']);

        $response = $this->withHeaders($this->authHeader())
                         ->getJson('/api/translations?key=welcome');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertTrue(collect($data)->every(
            fn($t) => str_contains($t['key'], 'welcome')
        ));
    }

    public function test_can_search_by_tag(): void
    {
        $tag         = Tag::factory()->create(['name' => 'mobile']);
        $translation = Translation::factory()->create();
        $translation->tags()->sync([$tag->id]);

        $response = $this->withHeaders($this->authHeader())
                         ->getJson('/api/translations?tag=mobile');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_unauthenticated_user_cannot_access_translations(): void
    {
        $response = $this->getJson('/api/translations');
        $response->assertStatus(401);
    }
}