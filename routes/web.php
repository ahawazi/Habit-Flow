<?php

use App\Http\Controllers\HabitController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HabitController::class, 'index'])->name('home');
Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
Route::post('/habits/{habit}/toggle', [HabitController::class, 'toggleDay'])->name('habits.toggle');
Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
