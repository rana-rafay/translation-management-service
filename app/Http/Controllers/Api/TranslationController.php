<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTranslationRequest;
use App\Http\Requests\UpdateTranslationRequest;
use App\Http\Resources\TranslationResource;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TranslationController extends Controller
{
    public function __construct(private readonly TranslationService $translationService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters      = $request->only(['locale', 'key', 'content', 'tag']);
        $translations = $this->translationService->getAll($filters);

        return TranslationResource::collection($translations);
    }

    public function show(int $id): TranslationResource
    {
        $translation = $this->translationService->findById($id);
        return new TranslationResource($translation);
    }

    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $translation = $this->translationService->create($request->validated());
        return response()->json([
            'message' => 'Translation created successfully',
            'data'    => new TranslationResource($translation),
        ], 201);
    }

    public function update(UpdateTranslationRequest $request, int $id): JsonResponse
    {
        $translation = $this->translationService->update($id, $request->validated());
        return response()->json([
            'message' => 'Translation updated successfully',
            'data'    => new TranslationResource($translation),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->translationService->delete($id);
        return response()->json(['message' => 'Translation deleted successfully']);
    }

    public function export(string $locale): JsonResponse
    {
        $translations = $this->translationService->export($locale);
        return response()->json($translations);
    }
}