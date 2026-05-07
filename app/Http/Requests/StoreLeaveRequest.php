<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'employee_id' => 
            'nullable|exists:employees,id',

            'leave_type' =>
            'nullable|string|max:255',

            'start_date' =>
            'nullable|date',

            'end_date' =>
            'nullable|date|after_or_equal:start_date',

            'reason' =>
            'nullable|string',

            'status' =>
            'nullable|in:pending,approved,rejected',
        ];
    }
}
