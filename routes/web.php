<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForumCommentController;
use App\Http\Controllers\ForumController;
use Illuminate\Support\Facades\Route;



Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/create', [AdminController::class, 'create'])->name('createUser');
    Route::post('/store', [AdminController::class, 'store'])->name('storeUser');
    Route::get('/edit/{id}', [AdminController::class, 'edit'])->name('editUser');
    Route::put('/update/{id}', [AdminController::class, 'update'])->name('updateUser');
    Route::delete('/delete/{id}', [AdminController::class, 'destroy'])->name('deleteUser');
});

Route::middleware(['auth'])->group(function () {
    Route::middleware(['subscribed'])->group(function () {
        Route::middleware(['organizer'])->group(function () {
            Route::get('/competitions/create', [CompetitionController::class, 'create'])->name('competitions.create');
            Route::post('/competitions', [CompetitionController::class, 'store'])->name('competitions.store');
            Route::get('/students/{student}/edit', [CompetitionController::class, 'editStudent'])->name('students.edit');
            Route::put('/students/{student}', [CompetitionController::class, 'updateStudent'])->name('students.update');
            Route::delete('/students/{student}', [CompetitionController::class, 'deleteStudent'])->name('students.destroy');

                       
            Route::get('/forums', [ForumController::class, 'index'])->name('forums.index');
            Route::get('/forums/{forum}', [ForumController::class, 'show'])->name('forums.show');
            Route::post('/forums/{forum}/comments', [ForumCommentController::class, 'store'])
                ->name('forums.comments.store');
            Route::put('/forums/{forum}/comments/{comment}', [ForumCommentController::class, 'update'])
                ->name('forums.comments.update');
        });
        Route::middleware(['owner'])->group(function () {

            Route::get('competitions/{competition}/edit', [CompetitionController::class, 'edit'])->name('competitions.edit');
            Route::get('competitions/{competition}/export-registrations', [CompetitionController::class, 'exportRegistrations'])->name('competitions.exportRegistrations');
            Route::get('competitions/{competition}/import-registrations', [CompetitionController::class, 'showImportRegistrationsForm'])->name('competitions.showImportRegistrationsForm');
            Route::post('competitions/{competition}/import-registrations', [CompetitionController::class, 'importRegistrations'])->name('competitions.importRegistrations');
            Route::put('competitions/{competition}', [CompetitionController::class, 'update'])->name('competitions.update');
            Route::delete('competitions/{competition}', [CompetitionController::class, 'destroy'])->name('competitions.destroy');
 
            Route::get('/competitions/{competition}/register', [CompetitionController::class, 'showRegistrationForm'])->name('competitions.showRegisterForm');
            Route::post('/competitions/{competition}/register-students', [CompetitionController::class, 'registerStudents'])->name('competitions.registerStudents');


            Route::post('/competitions/{competition}/invite-coorganizer', 
                [CompetitionController::class, 'inviteCoorganizer'])
                ->name('competitions.inviteCoorganizer');

            Route::get('/competitions/{competition}/points/edit', [CompetitionController::class, 'editPoints'])->name('competitions.points.edit');
            Route::post('/competitions/{competition}/points', [CompetitionController::class, 'updatePoints'])->name('competitions.points.update');
            });



        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/competitions/events-json', [DashboardController::class, 'eventsJson'])
        ->name('competitions.eventsJson');
    
     });
});



Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/competitions', [CompetitionController::class, 'index'])->name('competitions.index');
Route::get('/competitions/{competition}', [CompetitionController::class, 'show'])->name('competitions.show');
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/auth.php';