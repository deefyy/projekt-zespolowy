<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForumCommentController;
use App\Http\Controllers\ForumController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index']) ->middleware(['auth', 'verified']) ->name('dashboard');


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

Route::middleware(['auth'])->group(function () {
    Route::get('/competitions', [CompetitionController::class, 'index'])->name('competitions.index');
    Route::get('/competitions/create', [CompetitionController::class, 'create'])->name('competitions.create');
    Route::get('/competitions/{competition}', [CompetitionController::class, 'show'])->name('competitions.show');
    Route::get('competitions/{competition}/edit', [CompetitionController::class, 'edit'])->name('competitions.edit');
    Route::put('competitions/{competition}', [CompetitionController::class, 'update'])->name('competitions.update');
    Route::delete('competitions/{competition}', [CompetitionController::class, 'destroy'])->name('competitions.destroy');
    Route::post('/competitions', [CompetitionController::class, 'store'])->name('competitions.store');
    Route::get('/competitions/{competition}/register', [CompetitionController::class, 'showRegistrationForm'])->name('competitions.showRegisterForm');
    Route::post('/competitions/{competition}/register-students', [CompetitionController::class, 'registerStudents'])->name('competitions.registerStudents');
    Route::get('/students/{student}/edit', [CompetitionController::class, 'editStudent'])->name('students.edit');
    Route::put('/students/{student}', [CompetitionController::class, 'updateStudent'])->name('students.update');
    Route::delete('/students/{student}', [CompetitionController::class, 'deleteStudent'])->name('students.destroy');

    // Trasy forum (postów)
    Route::get('/forums', [ForumController::class, 'index'])->name('forums.index');
    Route::get('/forums/{forum}', [ForumController::class, 'show'])->name('forums.show');
    // (opcjonalnie, jeśli autor konkursu może tworzyć nowe wątki forum: Route::get('/forums/create'), Route::post('/forums'), itp.)

    // Trasy dla komentarzy na forum (zagnieżdżone w ramach konkretnego posta forum)
    Route::post('/forums/{forum}/comments', [ForumCommentController::class, 'store'])
         ->name('forums.comments.store');
    Route::put('/forums/{forum}/comments/{comment}', [ForumCommentController::class, 'update'])
         ->name('forums.comments.update');
});


require __DIR__.'/auth.php';
