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
        Schema::create('value_types', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->decimal('interest_rate', 5, 2);
            $table->enum('direction', ['ASC', 'DESC'])->default('ASC');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('value_types');
    }
};
