<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'total_amount',
        'paid_amount',
        'type',
        'category',
        'reference_no',
        'date_start',
        'date_end',
        'payment_due',
        'pay_in',
        'payment_mode',
        'is_recurring',
        'recurring_cycle',
        'description',
        'attachment',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_due' => 'integer',
            'is_recurring' => 'boolean',
            'total_amount' => 'decimal:4',
            'paid_amount' => 'decimal:4',
        ];
    }
}
