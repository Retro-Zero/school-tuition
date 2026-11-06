<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Students, their coupons and their enrolments.
 *
 * V1 kept a student's 14 subject values as columns on `users`
 * (summer_physics … extra_philosophy). They are rows here, so a student can take
 * any subset of subjects and the price list can change per grade/term.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_year_id')->constrained()->restrictOnDelete();
            $table->string('code');                       // فرهنگی دهم و یازدهم
            $table->unsignedBigInteger('amount')->default(0); // discount in ریال
            $table->timestamps();

            $table->unique(['financial_year_id', 'code']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_year_id')->constrained()->restrictOnDelete();
            $table->string('national_id', 20);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 20)->nullable();
            $table->foreignId('grade_level_id')->constrained()->restrictOnDelete();
            $table->foreignId('study_field_id')->constrained()->restrictOnDelete();

            $table->boolean('is_active')->default(true);  // V1: status enum(active,inactive)
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();

            // one-off amounts carried by the student record.
            // Signed: the V1 data contains a negative carry-over (a 7,000,000 ریال credit),
            // so a "debt" column that cannot go below zero loses money.
            $table->bigInteger('council_amount')->default(0);      // شورا
            $table->bigInteger('olympiad_amount')->default(0);     // المپیاد
            $table->bigInteger('carry_over_debt')->default(0);     // بدهی سال قبل (منفی = بستانکار)

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['financial_year_id', 'national_id']);
            $table->index(['financial_year_id', 'grade_level_id', 'study_field_id']);
            $table->index(['financial_year_id', 'last_name']);
        });

        Schema::create('enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->enum('term', ['summer', 'extra']);   // تابستان / کلاسهای فوق العاده
            $table->unsignedSmallInteger('quantity')->default(0); // جلسات
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'term']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrolments');
        Schema::dropIfExists('students');
        Schema::dropIfExists('coupons');
    }
};
