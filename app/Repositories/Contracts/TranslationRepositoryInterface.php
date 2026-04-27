<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TranslationRepositoryInterface
{
    public function getAll(array $filters): LengthAwarePaginator;
    public function findById(int $id): object;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function delete(int $id): bool;
    public function getByLocale(string $locale): Collection;
}
