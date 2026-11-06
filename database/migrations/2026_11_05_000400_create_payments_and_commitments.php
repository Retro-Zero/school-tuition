<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Money movements.
 *
 * - payments    : what was actually received (V1 `transactions`)
 * - commitments : the monthly payment agreement (V1 `payment_agreement`, which had
 *                 mehr/aban/.../khordad as nine columns — months are rows now)
 *
 * Financial history is never cascade-deleted: deleting a student soft-deletes the
 * record and leaves payments intact; FKs use restrict.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('financial_year_id')->constrained()->restrictOnDelete();
            $table->bigInteger('amount');                         // ریال (منفی = برگشت وجه)
            $table->enum('method', ['pos', 'bank'])->default('pos');
            $table->date('paid_on');                              // Gregorian; V1 stored "18 مرداد 1402"
            $table->string('reference')->nullable();              // شماره پیگیری / فیش
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['financial_year_id', 'paid_on']);
            $table->index('student_id');
        });

        Schema::create('commitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained()->restrictOnDelete();
            $table->date('due_on');
            $table->bigInteger('amount')->default(0);
            $table->string('label')->nullable();                  // مهر ...
            $table->timestamps();

            $table->unique(['student_id', 'due_on']);
            $table->index(['financial_year_id', 'due_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commitments');
        Schema::dropIfExists('payments');
    }
};
