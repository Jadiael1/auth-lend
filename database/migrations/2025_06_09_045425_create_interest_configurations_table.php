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
        Schema::create('interest_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_flag_id');
            $table->foreign('card_flag_id')->references('id')->on('card_flags')->onDelete('cascade');
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unsignedBigInteger('value_type_id');
            $table->foreign('value_type_id')->references('id')->on('value_types')->onDelete('cascade');
            $table->integer('installments');
            $table->decimal('interest_rate', 5, 2);
            $table->timestampsTz();

            $table->unique(['card_flag_id', 'store_id', 'value_type_id', 'installments'], 'unique_interest_configuration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_configurations');
    }
};
