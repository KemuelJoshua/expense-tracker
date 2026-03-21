<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_name' => ['required', 'string', 'max:255'],
            'account_type' => ['required', 'string', 'max:100'],
            'balance' => ['required', 'numeric', 'min:0'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'account_name.required' => 'Account name is required.',
            'account_type.required' => 'Account type is required.',
            'balance.required' => 'Current balance is required.',
            'initial_balance.required' => 'Initial balance is required.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be a valid 3-letter code.',
        ];
    }
}
