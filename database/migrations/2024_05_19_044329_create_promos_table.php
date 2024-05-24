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
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('promo_code');
            $table->date('promo_expiry_date');
            $table->integer('discount');
            $table->integer('minimum')->default(0);
            $table->integer('maximum')->default(0); // -1 == no limit
            $table->integer('max_use')->default(0);
            $table->integer('max_use_per_user')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo');
    }
};
