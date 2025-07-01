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
        Schema::create('card_flag_installment_limits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_flag_id');
            $table->foreign('card_flag_id')->references('id')->on('card_flags')->onDelete('cascade');
            $table->integer('installments')->unsigned();
            $table->decimal('min_value', 12, 2)->unsigned();
            $table->timestampsTz();

            $table->unique(['card_flag_id', 'installments'], 'unique_installment_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_flag_installment_limits');
    }
};
