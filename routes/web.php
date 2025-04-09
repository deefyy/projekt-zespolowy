<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/create', [AdminController::class, 'create'])->name('createUser');
    Route::post('/store', [AdminController::class, 'store'])->name('storeUser');
    Route::get('/edit/{id}', [AdminController::class, 'edit'])->name('editUser');
    Route::put('/update/{id}', [AdminController::class, 'update'])->name('updateUser');
    Route::delete('/delete/{id}', [AdminController::class, 'destroy'])->name('deleteUser');
});

require __DIR__.'/auth.php';
