<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BlogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => Str::squish((string) $this->query('q')) ?: null,
            'category' => trim((string) $this->query('category')) ?: null,
        ]);
    }

    public function searchTerm(): string
    {
        return (string) $this->validated('q', '');
    }

    public function categorySlug(): string
    {
        return (string) $this->validated('category', '');
    }
}
