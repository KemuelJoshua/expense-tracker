<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'type' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'date_start' => ['required', 'date'],
            'date_end' => ['nullable', 'date', 'after_or_equal:date_start'],
            'payment_due' => ['nullable', 'date'],
            'pay_in' => ['required', 'string', 'in:first,second'],
            'is_recurring' => ['boolean'],
            'recurring_cycle' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Expense name is required.',
            'total_amount.required' => 'Total amount is required.',
            'pay_in.required' => 'Pay in is required.',
            'pay_in.in' => 'Pay in must be either first or second.',
            'date_end.after_or_equal' => 'End date must be the same as or after the start date.',
        ];
    }
}
