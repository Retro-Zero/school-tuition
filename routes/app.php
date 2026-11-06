<?php

use App\Http\Controllers\CommitmentController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DebtorController;
use App\Http\Controllers\PaidController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

/*
| Everything that lives inside a financial year: /y/{year}/...
| Loaded from bootstrap/app.php, behind `auth`, with scoped bindings so a student
| from another year cannot be reached through this year's URL.
*/

Route::middleware('auth')->prefix('y/{year}')->scopeBindings()->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('report');

    // students
    Route::get('students', [StudentController::class, 'index'])->name('students.index');
    Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('students', [StudentController::class, 'store'])->name('students.store');
    Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::patch('students/{student}/status', [StudentController::class, 'toggle'])->name('students.toggle');
    Route::get('students/{student}/statement', [StudentController::class, 'statement'])->name('students.statement');

    // payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('students/{student}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('students/{student}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // lists
    Route::get('debtors', [DebtorController::class, 'index'])->name('debtors.index');
    Route::get('paid', [PaidController::class, 'index'])->name('paid.index');

    // coupons
    Route::get('coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::get('coupons/students', [CouponController::class, 'students'])->name('coupons.students');
    Route::get('coupons/create', [CouponController::class, 'create'])->name('coupons.create');
    Route::post('coupons', [CouponController::class, 'store'])->name('coupons.store');
    Route::get('coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
    Route::put('coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
    Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');

    // monthly commitments
    Route::get('commitments/{month?}', [CommitmentController::class, 'index'])->name('commitments.index');
    Route::put('commitments/{commitment}', [CommitmentController::class, 'update'])->name('commitments.update');
});
