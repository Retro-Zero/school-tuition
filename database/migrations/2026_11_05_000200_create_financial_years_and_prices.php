<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Financial years and the price list for each year.
 *
 * V1 had a single `prices` row per year with Persian grade columns plus
 * ten_summer/ten_extra/... → every new grade meant 3 new columns and edits in six
 * code paths. Here the price list is (price book → grade → term) rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();                 // 1402-1403
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_open')->default(true);        // accept new students/payments
            $table->timestamps();
        });

        Schema::create('price_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_year_id')->unique()
                ->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('insurance_amount')->default(0); // ریال
            $table->timestamps();
        });

        // Base tuition per grade for the year (V1: prices.دهم / یازدهم / دوازدهم)
        Schema::create('base_tuitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamps();

            $table->unique(['price_book_id', 'grade_level_id']);
        });

        // Per-session price for summer/extra classes (V1: ten_summer, ten_extra, ...)
        Schema::create('unit_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->restrictOnDelete();
            $table->enum('term', ['summer', 'extra']);
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamps();

            $table->unique(['price_book_id', 'grade_level_id', 'term']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_prices');
        Schema::dropIfExists('base_tuitions');
        Schema::dropIfExists('price_books');
        Schema::dropIfExists('financial_years');
    }
};
