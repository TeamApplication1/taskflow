<?php

use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\TaskAssignmentDashboardController;
use App\Http\Controllers\TaskEntityController;
use App\Http\Controllers\TaskNotificationController;
use App\Http\Controllers\TaskSourceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {

    // الصفحة الرئيسية = لوحة متابعة التكليفات
    Route::get('/dashboard', [TaskAssignmentDashboardController::class, 'index'])->name('dashboard');

    Route::view('profile', 'profile')->name('profile');

    // -----------------------------------------------------------------
    // متابعة التكليفات
    // -----------------------------------------------------------------

    // لازم قبل resource عشان تتسجل قبل tasks/{task}
    Route::get('tasks/dashboard', [TaskAssignmentDashboardController::class, 'index'])
        ->name('tasks.dashboard');

    Route::get('tasks/{task}/document', [TaskAssignmentController::class, 'showDocument'])
        ->name('tasks.document');

    Route::resource('tasks', TaskAssignmentController::class)
        ->parameters(['tasks' => 'task']);

    Route::prefix('notifications')->name('task-notifications.')->group(function () {
        Route::get('/recent', [TaskNotificationController::class, 'recent'])->name('recent');
        Route::get('/{id}/open', [TaskNotificationController::class, 'open'])->name('open');
        Route::post('/mark-all-read', [TaskNotificationController::class, 'markAllRead'])->name('mark-all-read');
    });

    // مصادر التكليف
    Route::middleware('can:tasks.view')->group(function () {
        Route::get('task-sources', [TaskSourceController::class, 'index'])->name('task-sources.index');
    });
    Route::middleware('can:tasks.create')->group(function () {
        Route::get('task-sources/create', [TaskSourceController::class, 'create'])->name('task-sources.create');
        Route::post('task-sources', [TaskSourceController::class, 'store'])->name('task-sources.store');
    });
    Route::middleware('can:tasks.edit')->group(function () {
        Route::get('task-sources/{taskSource}/edit', [TaskSourceController::class, 'edit'])->name('task-sources.edit');
        Route::put('task-sources/{taskSource}', [TaskSourceController::class, 'update'])->name('task-sources.update');
    });
    Route::middleware('can:tasks.delete')->group(function () {
        Route::delete('task-sources/{taskSource}', [TaskSourceController::class, 'destroy'])->name('task-sources.destroy');
    });

    // الجهات المختصة للتعامل
    Route::middleware('can:tasks.view')->group(function () {
        Route::get('task-entities', [TaskEntityController::class, 'index'])->name('task-entities.index');
    });
    Route::middleware('can:tasks.create')->group(function () {
        Route::get('task-entities/create', [TaskEntityController::class, 'create'])->name('task-entities.create');
        Route::post('task-entities', [TaskEntityController::class, 'store'])->name('task-entities.store');
    });
    Route::middleware('can:tasks.edit')->group(function () {
        Route::get('task-entities/{taskEntity}/edit', [TaskEntityController::class, 'edit'])->name('task-entities.edit');
        Route::put('task-entities/{taskEntity}', [TaskEntityController::class, 'update'])->name('task-entities.update');
    });
    Route::middleware('can:tasks.delete')->group(function () {
        Route::delete('task-entities/{taskEntity}', [TaskEntityController::class, 'destroy'])->name('task-entities.destroy');
    });
});

require __DIR__.'/auth.php';
