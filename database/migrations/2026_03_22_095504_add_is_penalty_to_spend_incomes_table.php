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
        Schema::table('spend_incomes', function (Blueprint $table): void {
            $table->boolean('is_penalty')->default(false)->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spend_incomes', function (Blueprint $table): void {
            $table->dropColumn('is_penalty');
        });
    }
};
