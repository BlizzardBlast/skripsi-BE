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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity')->nullable();
            $table->foreignId('product_id')->nullable()->references('id')->on('products')->onDelete('no action')->onUpdate('no action');

            $table->foreignId('order_id')->nullable()->references('id')->on('orders')->onDelete('no action')->onUpdate('no action');
            $table->foreignId('user_id')->nullable()->references('id')->on('users')->onDelete('no action')->onUpdate('no action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
