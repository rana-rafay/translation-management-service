<?php

namespace Tests\Feature;

use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_export_translations_by_locale(): void
    {
        Translation::factory()->count(5)->create(['locale' => 'en']);

        $response = $this->getJson('/api/export/en');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'locale',
                     'data',
                 ]);
    }

    public function test_export_returns_correct_locale(): void
    {
        Translation::factory()->create([
            'locale' => 'en',
            'key'    => 'welcome_message',
            'value'  => 'Welcome!',
        ]);

        Translation::factory()->create([
            'locale' => 'fr',
            'key'    => 'welcome_message',
            'value'  => 'Bienvenue!',
        ]);

        $response = $this->getJson('/api/export/en');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('welcome_message', $data);
        $this->assertEquals('Welcome!', $data['welcome_message']);
    }

    public function test_export_is_cached(): void
    {
        Translation::factory()->count(5)->create(['locale' => 'en']);

        Cache::shouldReceive('remember')
             ->once()
             ->andReturn([]);

        $this->getJson('/api/export/en');
    }

    public function test_export_cache_is_cleared_on_translation_update(): void
    {
        $user  = \App\Models\User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $translation = Translation::factory()->create([
            'locale' => 'en',
            'key'    => 'welcome_message',
            'value'  => 'Welcome!',
        ]);

        // First export to cache it
        $this->getJson('/api/export/en');

        // Update the translation
        $this->withHeader('Authorization', 'Bearer ' . $token)
             ->putJson('/api/translations/' . $translation->id, [
                 'value' => 'Welcome back!',
             ]);

        // Export again — should have updated value
        $response = $this->getJson('/api/export/en');
        $data     = $response->json('data');

        $this->assertEquals('Welcome back!', $data['welcome_message']);
    }

    public function test_export_does_not_require_authentication(): void
    {
        $response = $this->getJson('/api/export/en');
        $response->assertStatus(200);
    }

    public function test_export_response_time_is_under_500ms(): void
    {
        Translation::factory()->count(1000)->create(['locale' => 'en']);

        $start    = microtime(true);
        $response = $this->getJson('/api/export/en');
        $end      = microtime(true);

        $responseTime = ($end - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(500, $responseTime, 'Export response time exceeded 500ms');
    }
}