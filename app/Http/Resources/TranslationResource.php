<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranslationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'locale'     => $this->locale,
            'key'        => $this->key,
            'value'      => $this->value,
            'tags'       => $this->whenLoaded('tags', function () {
                return $this->tags->map(fn($tag) => [
                    'id'   => $tag->id,
                    'name' => $tag->name,
                ]);
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}