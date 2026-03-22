<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table): void {
            $table->string('balance', 1024)->change();
            $table->string('initial_balance', 1024)->change();
            $table->string('account_number', 1024)->nullable()->change();
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->string('total_amount', 1024)->change();
            $table->string('paid_amount', 1024)->nullable()->change();
        });

        Schema::table('spend_incomes', function (Blueprint $table): void {
            $table->string('amount', 1024)->change();
        });

        $this->encryptNumericTableColumns('accounts', ['balance', 'initial_balance']);
        $this->encryptNumericTableColumns('expenses', ['total_amount', 'paid_amount']);
        $this->encryptNumericTableColumns('spend_incomes', ['amount']);
        $this->encryptStringTableColumn('accounts', 'account_number');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->decryptNumericTableColumns('accounts', ['balance', 'initial_balance']);
        $this->decryptNumericTableColumns('expenses', ['total_amount', 'paid_amount']);
        $this->decryptNumericTableColumns('spend_incomes', ['amount']);
        $this->decryptStringTableColumn('accounts', 'account_number');

        Schema::table('accounts', function (Blueprint $table): void {
            $table->decimal('balance', 15, 4)->change();
            $table->decimal('initial_balance', 15, 4)->change();
            $table->string('account_number')->nullable()->change();
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->decimal('total_amount', 15, 4)->change();
            $table->decimal('paid_amount', 15, 4)->default(0)->change();
        });

        Schema::table('spend_incomes', function (Blueprint $table): void {
            $table->decimal('amount', 15, 4)->change();
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function encryptNumericTableColumns(string $table, array $columns): void
    {
        DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $columns): void {
            foreach ($rows as $row) {
                $updates = [];

                foreach ($columns as $column) {
                    $value = $row->{$column};

                    if ($value === null || $value === '') {
                        continue;
                    }

                    $updates[$column] = Crypt::encryptString(number_format((float) $value, 4, '.', ''));
                }

                if ($updates !== []) {
                    DB::table($table)->where('id', $row->id)->update($updates);
                }
            }
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function decryptNumericTableColumns(string $table, array $columns): void
    {
        DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $columns): void {
            foreach ($rows as $row) {
                $updates = [];

                foreach ($columns as $column) {
                    $value = $row->{$column};

                    if ($value === null || $value === '') {
                        $updates[$column] = null;

                        continue;
                    }

                    $updates[$column] = number_format((float) Crypt::decryptString($value), 4, '.', '');
                }

                DB::table($table)->where('id', $row->id)->update($updates);
            }
        });
    }

    private function encryptStringTableColumn(string $table, string $column): void
    {
        DB::table($table)->whereNotNull($column)->orderBy('id')->chunkById(100, function ($rows) use ($table, $column): void {
            foreach ($rows as $row) {
                DB::table($table)
                    ->where('id', $row->id)
                    ->update([$column => Crypt::encryptString((string) $row->{$column})]);
            }
        });
    }

    private function decryptStringTableColumn(string $table, string $column): void
    {
        DB::table($table)->whereNotNull($column)->orderBy('id')->chunkById(100, function ($rows) use ($table, $column): void {
            foreach ($rows as $row) {
                DB::table($table)
                    ->where('id', $row->id)
                    ->update([$column => Crypt::decryptString((string) $row->{$column})]);
            }
        });
    }
};
