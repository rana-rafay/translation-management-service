<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => 'required|string|max:10',
            'key'    => 'required|string|max:255|unique:translations,key,NULL,id,locale,' . $this->locale,
            'value'  => 'required|string',
            'tags'   => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'locale.required' => 'Locale is required e.g. en, fr, es',
            'key.required'    => 'Translation key is required',
            'key.unique'      => 'This key already exists for the given locale',
            'value.required'  => 'Translation value is required',
            'tags.*.exists'   => 'One or more tags do not exist',
        ];
    }
}