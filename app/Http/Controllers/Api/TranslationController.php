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
use OpenApi\Attributes as OA;

class TranslationController extends Controller
{
    public function __construct(
        private readonly TranslationService $translationService
    ) {}

    #[OA\Get(
        path: '/api/translations',
        summary: 'List and search translations',
        security: [['bearerAuth' => []]],
        tags: ['Translations'],
        parameters: [
            new OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'en'),
            new OA\Parameter(name: 'key', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'welcome'),
            new OA\Parameter(name: 'content', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'Welcome'),
            new OA\Parameter(name: 'tag', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'mobile'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of translations'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters      = $request->only(['locale', 'key', 'content', 'tag']);
        $translations = $this->translationService->getAll($filters);
        return TranslationResource::collection($translations);
    }

    #[OA\Post(
        path: '/api/translations',
        summary: 'Create a new translation',
        security: [['bearerAuth' => []]],
        tags: ['Translations'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['locale', 'key', 'value'],
                properties: [
                    new OA\Property(property: 'locale', type: 'string', example: 'en'),
                    new OA\Property(property: 'key', type: 'string', example: 'welcome_message'),
                    new OA\Property(property: 'value', type: 'string', example: 'Welcome!'),
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Translation created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $translation = $this->translationService->create($request->validated());

        return response()->json([
            'message' => 'Translation created successfully',
            'data'    => new TranslationResource($translation),
        ], 201);
    }

    #[OA\Get(
        path: '/api/translations/{id}',
        summary: 'View a translation',
        security: [['bearerAuth' => []]],
        tags: ['Translations'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Translation details'),
            new OA\Response(response: 404, description: 'Translation not found'),
        ]
    )]
    public function show(int $id): TranslationResource
    {
        return new TranslationResource(
            $this->translationService->findById($id)
        );
    }

    #[OA\Put(
        path: '/api/translations/{id}',
        summary: 'Update a translation',
        security: [['bearerAuth' => []]],
        tags: ['Translations'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'locale', type: 'string', example: 'en'),
                    new OA\Property(property: 'key', type: 'string', example: 'welcome_message'),
                    new OA\Property(property: 'value', type: 'string', example: 'Welcome back!'),
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'integer'), example: [1]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Translation updated successfully'),
            new OA\Response(response: 404, description: 'Translation not found'),
        ]
    )]
    public function update(UpdateTranslationRequest $request, int $id): JsonResponse
    {
        $translation = $this->translationService->update($id, $request->validated());

        return response()->json([
            'message' => 'Translation updated successfully',
            'data'    => new TranslationResource($translation),
        ]);
    }

    #[OA\Delete(
        path: '/api/translations/{id}',
        summary: 'Delete a translation',
        security: [['bearerAuth' => []]],
        tags: ['Translations'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Translation deleted successfully'),
            new OA\Response(response: 404, description: 'Translation not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $this->translationService->delete($id);

        return response()->json([
            'message' => 'Translation deleted successfully',
        ]);
    }

    #[OA\Get(
        path: '/api/export/{locale}',
        summary: 'Export all translations for a locale',
        tags: ['Export'],
        parameters: [
            new OA\Parameter(name: 'locale', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'en'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Translations exported successfully'),
        ]
    )]
    public function export(string $locale): JsonResponse
    {
        $translations = $this->translationService->export($locale);

        return response()->json([
            'locale' => $locale,
            'data'   => $translations,
        ]);
    }
}