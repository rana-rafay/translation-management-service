<?php

namespace App\Services;

use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TranslationService
{
    public function __construct(private readonly TranslationRepositoryInterface $translationRepository) {}

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
        return $this->translationRepository->create($data);
    }

    public function update(int $id, array $data): object
    {
        return $this->translationRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->translationRepository->delete($id);
    }

    public function export(string $locale): Collection
    {
        return $this->translationRepository->getByLocale($locale);
    }
}