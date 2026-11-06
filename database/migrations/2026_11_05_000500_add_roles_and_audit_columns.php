<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structure the app needed before a second person ever logs in:
 * a role on staff accounts, and an author on student records.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // admin | accountant | viewer  (single admin today, but the column exists now)
            $table->string('role', 20)->default('admin')->after('name');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('is_active')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
