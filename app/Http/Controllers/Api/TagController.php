<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TagController extends Controller
{
    #[OA\Get(
        path: '/api/tags',
        summary: 'List all tags',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        responses: [
            new OA\Response(response: 200, description: 'List of tags'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(): JsonResponse
    {
        $tags = Tag::all();
        return response()->json($tags);
    }

    #[OA\Post(
        path: '/api/tags',
        summary: 'Create a new tag',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'mobile'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tag created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:tags,name',
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'message' => 'Tag created successfully',
            'data'    => $tag,
        ], 201);
    }

    #[OA\Get(
        path: '/api/tags/{id}',
        summary: 'View a tag',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tag details'),
            new OA\Response(response: 404, description: 'Tag not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);
        return response()->json($tag);
    }

    #[OA\Put(
        path: '/api/tags/{id}',
        summary: 'Update a tag',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'ios'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Tag updated successfully'),
            new OA\Response(response: 404, description: 'Tag not found'),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:tags,name,' . $id,
        ]);

        $tag->update($validated);

        return response()->json([
            'message' => 'Tag updated successfully',
            'data'    => $tag,
        ]);
    }

    #[OA\Delete(
        path: '/api/tags/{id}',
        summary: 'Delete a tag',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tag deleted successfully'),
            new OA\Response(response: 404, description: 'Tag not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully',
        ]);
    }
}