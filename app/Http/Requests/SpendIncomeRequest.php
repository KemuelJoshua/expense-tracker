<?php

namespace App\Http\Requests;

use App\Models\Accounts;
use App\Models\Expenses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpendIncomeRequest extends FormRequest
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
        $entryType = (string) $this->input('entry_type');
        $isPayroll = $this->boolean('is_payroll');

        return [
            'entry_type' => ['required', 'string', Rule::in(['spend', 'income'])],
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'expense_reference' => [
                Rule::requiredIf($entryType === 'spend'),
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->input('entry_type') !== 'spend' || $value === null || $value === '') {
                        return;
                    }

                    if ($value === 'others') {
                        return;
                    }

                    $exists = Expenses::query()
                        ->whereKey($value)
                        ->where('created_by', $this->user()?->id)
                        ->exists();

                    if (! $exists) {
                        $fail('The selected expense is invalid.');
                    }
                },
            ],
            'account_reference' => [
                Rule::requiredIf($entryType === 'income'),
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->input('entry_type') !== 'income' || $value === null || $value === '') {
                        return;
                    }

                    if ($value === 'others') {
                        return;
                    }

                    $exists = Accounts::query()
                        ->whereKey($value)
                        ->where('user_id', $this->user()?->id)
                        ->exists();

                    if (! $exists) {
                        $fail('The selected account is invalid.');
                    }
                },
            ],
            'is_payroll' => [Rule::requiredIf($entryType === 'income'), 'boolean'],
            'payroll_month' => [
                Rule::requiredIf($entryType === 'income' && $isPayroll),
                'nullable',
                'integer',
                'between:1,12',
            ],
            'payroll_year' => [
                Rule::requiredIf($entryType === 'income' && $isPayroll),
                'nullable',
                'integer',
                'min:2000',
                'max:2100',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'entry_type.required' => 'Please choose whether this is spend or income.',
            'transaction_date.required' => 'The transaction date is required.',
            'amount.required' => 'Amount is required.',
            'expense_reference.required' => 'Please select an expense or choose Others.',
            'account_reference.required' => 'Please select an account or choose Others.',
            'is_payroll.required' => 'Please specify if the income is from payroll.',
            'payroll_month.required' => 'Payroll month is required when the income is from payroll.',
            'payroll_year.required' => 'Payroll year is required when the income is from payroll.',
        ];
    }
}
