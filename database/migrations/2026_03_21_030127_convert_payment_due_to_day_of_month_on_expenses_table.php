<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedTinyInteger('payment_due_day')->nullable()->after('date_end');
        });

        DB::table('expenses')
            ->whereNotNull('payment_due')
            ->update([
                'payment_due_day' => DB::raw('DAY(payment_due)'),
            ]);

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('payment_due');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('payment_due_day', 'payment_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->date('payment_due_date')->nullable()->after('date_end');
        });

        DB::table('expenses')
            ->whereNotNull('payment_due')
            ->update([
                'payment_due_date' => DB::raw("STR_TO_DATE(CONCAT('2026-01-', LPAD(payment_due, 2, '0')), '%Y-%m-%d')"),
            ]);

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('payment_due');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('payment_due_date', 'payment_due');
        });
    }
};
