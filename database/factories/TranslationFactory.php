<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        return [
            'locale' => $this->faker->randomElement(['en', 'fr', 'es', 'ur', 'de']),
            'key'    => $this->generateUniqueKey(),
            'value'  => $this->faker->sentence(),
        ];
    }

    private function generateUniqueKey(): string
    {
        $prefixes = [
            'button',
            'label',
            'message',
            'error',
            'title',
            'placeholder',
            'tooltip',
            'header',
            'footer',
            'menu',
            'nav',
            'form',
        ];

        $prefix = $this->faker->randomElement($prefixes);
        $suffix = $this->faker->unique()->numerify('########');

        return $prefix . '_' . $suffix;
    }
}
