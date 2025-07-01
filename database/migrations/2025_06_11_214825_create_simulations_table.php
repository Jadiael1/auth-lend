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
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_with_interest', 10, 2);
            $table->decimal('interest_rate_by_type_of_amount', 5, 2);
            $table->decimal('interest_rate_by_number_of_installments', 5, 2);
            $table->integer('installments')->unsigned();
            $table->decimal('installment_value', 10, 2);
            $table->unsignedBigInteger('value_type_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('card_flag_id')->nullable();
            $table->string('ip')->nullable();
            $table->timestampsTz();
            $table->foreign('value_type_id')->references('id')->on('value_types')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->nullOnDelete();
            $table->foreign('card_flag_id')->references('id')->on('card_flags')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulations');
    }
};
