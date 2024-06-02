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
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->references('id')->on('products')->onDelete('no action')->onUpdate('no action');
            $table->string('acidity');  // Low || Medium || High
            $table->string('flavor'); // earthy || chocolate || fruit || nutty
            $table->string('aftertaste'); // complex || lingering || short
            $table->string('sweetness'); // faint || noticeable || rich
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