<?php
//
//use Illuminate\Database\Migrations\Migration;
//use Illuminate\Database\Schema\Blueprint;
//use Illuminate\Support\Facades\Schema;
//
//return new class extends Migration
//{
//    /**
//     * Run the migrations.
//     */
//    public function up(): void
//    {
//        Schema::create('commissions', function (Blueprint $table) {
//            $table->id();
//            $table->bigInteger('design_id');
//            $table->bigInteger('tailor_id');
//            $table->enum('status', [1,2,3]);
//            $table->date('start_date');
//            $table->date('end_date');
//            $table->timestamps();
//        });
//    }
//
//    /**
//     * Reverse the migrations.
//     */
//    public function down(): void
//    {
//        Schema::dropIfExists('commissions');
//    }
//};
