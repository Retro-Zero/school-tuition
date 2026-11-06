<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reference data that used to be hard-coded in the V1 schema
 * (Persian column names `دهم`/`یازدهم`/`دوزادهم`, subject columns, month columns).
 * Adding a grade, a field or a subject is now a row, not a migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();          // دهم / یازدهم / دوازدهم
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('study_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();          // تجربی / ریاضی / انسانی
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();          // فیزیک, شیمی, ...
            $table->string('key')->unique();           // physics, chemistry, ... (import/sync key)
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('study_fields');
        Schema::dropIfExists('grade_levels');
    }
};
