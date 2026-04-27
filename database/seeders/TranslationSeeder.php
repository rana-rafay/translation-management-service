<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    private const BATCH_SIZE  = 1000;
    private const TOTAL       = 100000;

    public function run(): void
    {
        $this->command->info('Seeding 100,000 translations...');
        $tagIds  = Tag::pluck('id')->toArray();
        $batches = self::TOTAL / self::BATCH_SIZE;
        $bar     = $this->command->getOutput()->createProgressBar($batches);
        $bar->start();

        for ($i = 0; $i < $batches; $i++) {
            $translations = Translation::factory()->count(self::BATCH_SIZE)->create();

            $translations->each(function ($translation) use ($tagIds) {
                $randomTags = array_rand(
                    array_flip($tagIds),
                    rand(1, count($tagIds))
                );

                $translation->tags()->sync(is_array($randomTags) ? $randomTags : [$randomTags]);
            });

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Done! 100,000 translations seeded successfully.');
    }
}
