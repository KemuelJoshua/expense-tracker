<?php

namespace Tests\Unit\Accounts;

use App\Http\Requests\AccountRequest;
use PHPUnit\Framework\TestCase;

class AccountRequestTest extends TestCase
{
    public function test_account_request_contains_expected_rules(): void
    {
        $request = new AccountRequest;
        $rules = $request->rules();

        $this->assertSame(['required', 'string', 'max:255'], $rules['account_name']);
        $this->assertSame(['required', 'string', 'size:3'], $rules['currency']);
        $this->assertSame(['boolean'], $rules['is_default']);
    }

    public function test_account_request_contains_expected_messages(): void
    {
        $request = new AccountRequest;
        $messages = $request->messages();

        $this->assertSame('Account name is required.', $messages['account_name.required']);
        $this->assertSame('Currency must be a valid 3-letter code.', $messages['currency.size']);
    }
}
