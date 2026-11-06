<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FinancialYearController;
use App\Http\Controllers\PriceController;
use Illuminate\Support\Facades\Route;

/*
| Authentication and the year-independent screens. Everything inside a financial
| year lives in routes/app.php.
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [FinancialYearController::class, 'index'])->name('years.index');
    Route::post('years', [FinancialYearController::class, 'store'])->name('years.store');
    Route::get('years/{year}/edit', [FinancialYearController::class, 'edit'])->name('years.edit');
    Route::put('years/{year}', [FinancialYearController::class, 'update'])->name('years.update');
    Route::delete('years/{year}', [FinancialYearController::class, 'destroy'])->name('years.destroy');

    Route::get('years/{year}/prices', [PriceController::class, 'edit'])->name('prices.edit');
    Route::put('years/{year}/prices', [PriceController::class, 'update'])->name('prices.update');
});
