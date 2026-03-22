<?php

namespace App\Models;

use App\Casts\EncryptedDecimal;
use Database\Factories\SpendIncomeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpendIncome extends Model
{
    /** @use HasFactory<SpendIncomeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entry_type',
        'transaction_date',
        'amount',
        'description',
        'expense_id',
        'account_id',
        'is_payroll',
        'payroll_month',
        'payroll_year',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => EncryptedDecimal::class.':4',
            'is_payroll' => 'boolean',
            'payroll_month' => 'integer',
            'payroll_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expenses::class, 'expense_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }
}
