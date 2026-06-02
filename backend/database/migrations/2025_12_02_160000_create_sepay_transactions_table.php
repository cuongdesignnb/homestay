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
        Schema::create('sepay_transactions', function (Blueprint $table) {
            $table->id();
            $table->text('gateway')->nullable();
            $table->text('transactionDate')->nullable();
            $table->text('accountNumber')->nullable();
            $table->text('code')->nullable();
            $table->text('content')->nullable();
            $table->text('transferType')->nullable();
            $table->text('transferAmount')->nullable();
            $table->text('accumulated')->nullable();
            $table->text('subAccount')->nullable();
            $table->text('referenceCode')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sepay_transactions');
    }
};
