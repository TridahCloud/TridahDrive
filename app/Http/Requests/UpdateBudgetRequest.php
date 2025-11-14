<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'category_id' => [
                'nullable',
                'exists:categories,id',
            ],
            'period_type' => [
                'required',
                'in:monthly,quarterly,yearly,custom',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Budget name is required.',
            'amount.required' => 'Budget amount is required.',
            'amount.min' => 'Budget amount must be at least 0.',
            'period_type.required' => 'Period type is required.',
            'start_date.required' => 'Start date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }
}

