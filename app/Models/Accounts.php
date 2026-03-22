<?php

namespace App\Models;

use App\Casts\EncryptedDecimal;
use App\Casts\NullableEncryptedString;
use Database\Factories\AccountsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accounts extends Model
{
    /** @use HasFactory<AccountsFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_name',
        'account_type',
        'balance',
        'initial_balance',
        'account_number',
        'bank_name',
        'currency',
        'is_active',
        'is_default',
        'user_id',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => EncryptedDecimal::class.':4',
            'initial_balance' => EncryptedDecimal::class.':4',
            'account_number' => NullableEncryptedString::class,
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
