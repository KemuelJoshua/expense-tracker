<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'is_recurring',
        'recurring_cycle',
        'description',
        'attachment',
        'created_by',
    ];
}
