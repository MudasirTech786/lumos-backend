<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'employee_code' =>
            'required|string|max:255',

            'user_id' =>
            'nullable|exists:users,id',

            'name' =>
            'required|string|max:255',

            'email' =>
            'nullable|email',

            'phone' =>
            'nullable|string',

            'department' =>
            'nullable|string',

            'designation' =>
            'nullable|string',

            'base_salary' =>
            'nullable|numeric',

            'hire_date' =>
            'nullable|date',

            'status' =>
            'required|string',

            'cnic' =>
            'nullable|string',

            'address' =>
            'nullable|string',

            'emergency_contact' =>
            'nullable|string',

            'profile_photo' =>
            'nullable|string',
        ];
    }
}
