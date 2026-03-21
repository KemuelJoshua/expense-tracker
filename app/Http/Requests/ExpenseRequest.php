<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $type = (string) $this->input('type');

        return [
            'name' => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'type' => ['required', 'string', Rule::in(['loan', 'subscription', 'utilities', 'others'])],
            'category' => ['nullable', 'string', 'max:100'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'date_start' => ['required', 'date'],
            'date_end' => [
                Rule::requiredIf($type === 'loan' && $this->input('payment_mode') === 'installment'),
                'nullable',
                'date',
                'after_or_equal:date_start',
            ],
            'payment_due' => ['nullable', 'integer', 'min:1', 'max:31'],
            'pay_in' => ['required', 'string', 'in:first,second'],
            'payment_mode' => [
                Rule::requiredIf(in_array($type, ['loan', 'utilities'], true)),
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($type): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $allowedModes = match ($type) {
                        'loan' => ['one_time', 'installment'],
                        'utilities' => ['fixed_monthly', 'variable_amount'],
                        default => [],
                    };

                    if ($allowedModes !== [] && ! in_array((string) $value, $allowedModes, true)) {
                        $fail("The {$attribute} selection is invalid for the selected expense type.");
                    }
                },
            ],
            'is_recurring' => ['boolean'],
            'recurring_cycle' => ['nullable', 'string', Rule::in(['monthly', 'yearly'])],
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
            'payment_mode.required' => 'Payment setup is required for the selected expense type.',
            'payment_due.integer' => 'Payment due must be a valid day of the month.',
            'payment_due.min' => 'Payment due must be between day 1 and 31.',
            'payment_due.max' => 'Payment due must be between day 1 and 31.',
            'date_end.after_or_equal' => 'End date must be the same as or after the start date.',
            'date_end.required' => 'End date is required for an installment loan.',
        ];
    }
}
