<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __construct(private readonly TranslationService $translationService) {}

    public function index(Request $request): JsonResponse
    {
        $filters      = $request->only(['locale', 'key', 'content', 'tag']);
        $translations = $this->translationService->getAll($filters);

        return response()->json($translations);
    }

    public function show(int $id): JsonResponse
    {
        $translation = $this->translationService->findById($id);
        return response()->json($translation);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'locale' => 'required|string|max:10',
            'key'    => 'required|string|max:255',
            'value'  => 'required|string',
            'tags'   => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ]);

        $translation = $this->translationService->create($validated);

        return response()->json([
            'message' => 'Translation created successfully',
            'data'    => $translation,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'locale' => 'sometimes|string|max:10',
            'key'    => 'sometimes|string|max:255',
            'value'  => 'sometimes|string',
            'tags'   => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ]);

        $translation = $this->translationService->update($id, $validated);

        return response()->json([
            'message' => 'Translation updated successfully',
            'data'    => $translation,
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