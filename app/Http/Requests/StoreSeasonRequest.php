<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-seasons') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'season_number' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
                'unique:seasons,season_number',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:seasons,slug',
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => [
                'required',
                Rule::in(['upcoming', 'active', 'completed']),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}