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
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hex_code', 7)->unique();
            $table->timestamps();
        });

        Schema::create('store_colors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('store_id');
            $table->bigInteger('color_id');
            $table->decimal('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colors');
        Schema::dropIfExists('store_colors');
    }
};
