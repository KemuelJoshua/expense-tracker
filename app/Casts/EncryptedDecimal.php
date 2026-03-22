<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class EncryptedDecimal implements CastsAttributes
{
    public function __construct(private readonly int $scale = 4) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value)) {
            return $this->normalizeDecimal($value);
        }

        try {
            return $this->normalizeDecimal(Crypt::decryptString($value));
        } catch (DecryptException) {
            return $this->normalizeDecimal($value);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::encryptString($this->normalizeDecimal($value));
    }

    private function normalizeDecimal(mixed $value): string
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException('EncryptedDecimal cast expects a numeric value.');
        }

        return number_format((float) $value, $this->scale, '.', '');
    }
}
