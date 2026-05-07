<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCrewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => 'required|string|max:255',

            'email' => 'nullable|email',

            'phone' => 'nullable|string',

            'designation' => 'nullable|string',

            'employment_type' => 'required|string',

            'basic_salary' => 'nullable|numeric',

            'rate_per_shift' => 'nullable|numeric',

            'hourly_rate' => 'nullable|numeric',

            'commission' => 'nullable|numeric',

            'home_allowance' => 'nullable|numeric',

            'fuel_allowance' => 'nullable|numeric',

            'others' => 'nullable|numeric',

            'skills' => 'nullable|array',

            'skills.*' => 'string',

            'notes' => 'nullable|string',

            'is_active' => 'boolean',
        ];
    }
}