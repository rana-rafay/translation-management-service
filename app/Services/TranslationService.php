<?php

namespace App\Services;

use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    private const CACHE_TTL    = 3600;
    private const CACHE_PREFIX = 'translations_export_';

    public function __construct(
        private readonly TranslationRepositoryInterface $translationRepository
    ) {}

    public function getAll(array $filters): LengthAwarePaginator
    {
        return $this->translationRepository->getAll($filters);
    }

    public function findById(int $id): object
    {
        return $this->translationRepository->findById($id);
    }

    public function create(array $data): object
    {
        $translation = $this->translationRepository->create($data);

        $this->clearExportCache($translation->locale);

        return $translation;
    }

    public function update(int $id, array $data): object
    {
        $translation = $this->translationRepository->update($id, $data);

        $this->clearExportCache($translation->locale);

        return $translation;
    }

    public function delete(int $id): bool
    {
        $translation = $this->translationRepository->findById($id);
        $locale      = $translation->locale;

        $result = $this->translationRepository->delete($id);

        $this->clearExportCache($locale);

        return $result;
    }

    public function export(string $locale): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . $locale,
            self::CACHE_TTL,
            fn() => $this->translationRepository
                ->getByLocale($locale)
                ->pluck('value', 'key')
                ->toArray()
        );
    }

    private function clearExportCache(string $locale): void
    {
        Cache::forget(self::CACHE_PREFIX . $locale);
    }
}
