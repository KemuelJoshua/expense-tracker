<?php

namespace App\Http\Requests;

use App\Models\Expenses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class CutoffPaymentRequest extends FormRequest
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
            'action_type' => ['required', 'string', 'in:payment,penalty'],
            'month' => ['required', 'date_format:Y-m'],
            'expense_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $exists = Expenses::query()
                        ->whereKey($value)
                        ->where('created_by', $this->user()?->id)
                        ->exists();

                    if (! $exists) {
                        $fail('The selected expense is invalid.');
                    }
                },
            ],
            'transaction_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $month = (string) $this->input('month');

                    if ($month === '') {
                        return;
                    }

                    try {
                        $selectedMonth = Carbon::createFromFormat('Y-m', $month);
                    } catch (\Throwable) {
                        return;
                    }

                    if (Carbon::parse((string) $value)->format('Y-m') !== $selectedMonth->format('Y-m')) {
                        $fail('Transaction date must be within the selected cutoff month.');
                    }
                },
            ],
            'amount' => ['required', 'numeric', 'min:0.0001'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action_type.required' => 'The cutoff action is required.',
            'month.required' => 'The cutoff month is required.',
            'month.date_format' => 'The cutoff month must use the YYYY-MM format.',
            'expense_id.required' => 'Please select an expense to pay.',
            'transaction_date.required' => 'Transaction date is required.',
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be greater than zero.',
        ];
    }
}
