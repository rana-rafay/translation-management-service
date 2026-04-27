<?php

namespace App\Repositories;

use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TranslationRepository implements TranslationRepositoryInterface
{
    public function __construct(private readonly Translation $model) {}

    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with('tags');

        if (!empty($filters['locale'])) {
            $query->where('locale', $filters['locale']);
        }
        
        if (!empty($filters['key'])) {
            $query->where('key', 'like', '%' . $filters['key'] . '%');
        }
        
        if (!empty($filters['content'])) {
            $query->where('value', 'like', '%' . $filters['content'] . '%');
        }
        
        if (!empty($filters['tag'])) { 
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('name', $filters['tag']);
            });
        }

        return $query->paginate(50);
    }

    public function findById(int $id): object
    {
        return $this->model->with('tags')->findOrFail($id);
    }

    public function create(array $data): object
    {
        $translation = $this->model->create([
            'locale' => $data['locale'],
            'key'    => $data['key'],
            'value'  => $data['value'],
        ]);

        if (!empty($data['tags'])) {
            $translation->tags()->sync($data['tags']);
        }

        return $translation->load('tags');
    }

    public function update(int $id, array $data): object
    {
        $translation = $this->model->findOrFail($id);
        $translation->update([
            'locale' => $data['locale'] ?? $translation->locale,
            'key'    => $data['key']    ?? $translation->key,
            'value'  => $data['value']  ?? $translation->value,
        ]);

        if (isset($data['tags'])) {
            $translation->tags()->sync($data['tags']);
        }

        return $translation->load('tags');
    }

    public function delete(int $id): bool
    {
        $translation = $this->model->findOrFail($id);
        return $translation->delete();
    }

    public function getByLocale(string $locale): Collection
    {
        return $this->model->where('locale', $locale)->get(['key', 'value']);
    }
}
