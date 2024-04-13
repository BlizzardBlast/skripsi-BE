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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subname');
            $table->string('origin');
            $table->string('characteristic');
            $table->string('type'); // Arabica || Robusta
            $table->integer('price');
            $table->text('description');

            $table->string('acidity');  // Sour || Neutral
            $table->string('mouthfeel');// Light || Heavy
            $table->string('sweetness');// Sweet || Bitter

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
