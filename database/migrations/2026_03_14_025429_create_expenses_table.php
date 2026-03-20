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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->decimal('total_amount', 15, 4);
            $table->decimal('paid_amount', 15, 4)->default(0);

            $table->string('type'); // loan, subscription, utilities, others
            $table->string('category')->nullable(); // internet, rent, software, etc

            $table->string('reference_no')->nullable(); // invoice number, loan ID

            $table->date('date_start');
            $table->date('date_end')->nullable();
            $table->date('payment_due')->nullable();

            $table->string('pay_in')->default('first'); // first or second

            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_cycle')->nullable(); // monthly, yearly

            $table->text('description')->nullable();
            $table->string('attachment')->nullable(); // receipt path

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
