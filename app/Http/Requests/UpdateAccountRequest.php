<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
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
        $driveId = $this->route('drive')->id;
        $accountId = $this->route('account')->id;

        return [
            'parent_id' => [
                'nullable',
                'exists:accounts,id',
            ],
            'account_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('accounts')->where(function ($query) use ($driveId) {
                    return $query->where('drive_id', $driveId);
                })->ignore($accountId),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'type' => [
                'required',
                'in:asset,liability,equity,revenue,expense',
            ],
            'subtype' => [
                'nullable',
                'string',
                'max:50',
            ],
            'description' => [
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
            'account_code.unique' => 'This account code already exists in this drive.',
            'account_code.required' => 'Account code is required.',
            'name.required' => 'Account name is required.',
            'type.required' => 'Account type is required.',
            'type.in' => 'Invalid account type.',
        ];
    }
}
