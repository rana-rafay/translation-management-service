<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => 'sometimes|string|max:10',
            'key'    => 'sometimes|string|max:255|unique:translations,key,' . $this->route('translation') . ',id,locale,' . $this->locale,
            'value'  => 'sometimes|string',
            'tags'   => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'key.unique'    => 'This key already exists for the given locale',
            'tags.*.exists' => 'One or more tags do not exist',
        ];
    }
}