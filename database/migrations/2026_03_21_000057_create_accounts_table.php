<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('account_name'); // e.g. BDO Savings, GCash Wallet
            $table->string('account_type'); // bank, e-wallet, cash, credit, etc.

            // Financial
            $table->decimal('balance', 15, 4)->default(0); // current balance
            $table->decimal('initial_balance', 15, 4)->default(0);

            // Optional Details
            $table->string('account_number')->nullable(); // bank account number
            $table->string('bank_name')->nullable(); // BDO, BPI, etc.
            $table->string('currency', 10)->default('PHP');

            // Status & Control
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            // Ownership (important if multi-user)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Extra
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
