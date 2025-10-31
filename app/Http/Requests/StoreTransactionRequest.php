<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'date' => [
                'required',
                'date',
            ],
            'description' => [
                'required',
                'string',
                'max:500',
            ],
            'reference' => [
                'nullable',
                'string',
                'max:100',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'type' => [
                'required',
                'in:income,expense,transfer,adjustment',
            ],
            'account_id' => [
                'required',
                'exists:accounts,id',
            ],
            'category_id' => [
                'nullable',
                'exists:categories,id',
            ],
            'payee' => [
                'nullable',
                'string',
                'max:255',
            ],
            'payment_method' => [
                'nullable',
                'in:cash,check,credit_card,debit_card,bank_transfer,other',
            ],
            'status' => [
                'nullable',
                'in:pending,cleared,reconciled',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Transaction date is required.',
            'description.required' => 'Transaction description is required.',
            'amount.required' => 'Transaction amount is required.',
            'amount.min' => 'Transaction amount must be greater than or equal to 0.',
            'type.required' => 'Transaction type is required.',
            'type.in' => 'Invalid transaction type.',
            'account_id.required' => 'Account is required.',
            'account_id.exists' => 'Selected account does not exist.',
        ];
    }
}
