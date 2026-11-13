<?php

use App\Http\Controllers\Admin\ClassRoomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LearningScheduleController;
use App\Http\Controllers\Admin\LearningSessionController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\StudentAttendanceController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Tutor\AttendanceController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TutorController;
use App\Http\Controllers\Admin\TryoutController as AdminTryoutController;
use App\Http\Controllers\Tutor\TryoutController as TutorTryoutController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('classes', ClassRoomController::class)->parameters(['classes' => 'classRoom']);
    Route::resource('learning-schedules', LearningScheduleController::class)
        ->parameters(['learning-schedules' => 'learningSchedule']);
    Route::post('learning-schedules/{learningSchedule}/activate', [LearningScheduleController::class, 'activate'])
        ->name('learning-schedules.activate');
    Route::post('learning-schedules/{learningSchedule}/deactivate', [LearningScheduleController::class, 'deactivate'])
        ->name('learning-schedules.deactivate');
    Route::resource('learning-sessions', LearningSessionController::class)
        ->parameters(['learning-sessions' => 'learningSession']);
    Route::patch('learning-sessions/{learningSession}/status', [LearningSessionController::class, 'updateStatus'])
        ->name('learning-sessions.update-status');
    Route::resource('subjects', SubjectController::class);
    Route::resource('tutors', TutorController::class);
    Route::resource('parents', ParentController::class)->parameters(['parents' => 'parent']);
    Route::resource('students', StudentController::class);
    Route::resource('tryouts', AdminTryoutController::class);
    Route::patch('tryouts/{tryout}/status', [AdminTryoutController::class, 'updateStatus'])
        ->name('tryouts.update-status');
    Route::get('student-attendances', [StudentAttendanceController::class, 'index'])
        ->name('student-attendances.index');
    Route::get('student-attendances/{learningSession}', [StudentAttendanceController::class, 'show'])
        ->name('student-attendances.show');
    Route::get('student-attendances/{learningSession}/edit', [StudentAttendanceController::class, 'edit'])
        ->name('student-attendances.edit');
    Route::put('student-attendances/{learningSession}', [StudentAttendanceController::class, 'update'])
        ->name('student-attendances.update');
});

Route::middleware(['auth', 'role:tutor'])->group(function () {
    Route::get('/tutor/dashboard', function () {
        return view('tutor.dashboard');
    })->name('tutor.dashboard');
    Route::get('/tutor/attendances', [AttendanceController::class, 'index'])
        ->name('tutor.attendances.index');
    Route::get('/tutor/attendances/{learningSession}/edit', [AttendanceController::class, 'edit'])
        ->name('tutor.attendances.edit');
    Route::put('/tutor/attendances/{learningSession}', [AttendanceController::class, 'update'])
        ->name('tutor.attendances.update');
    Route::resource('/tutor/tryouts', TutorTryoutController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
        ->names('tutor.tryouts');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');
});

Route::middleware(['auth', 'role:parent'])->group(function () {
    Route::get('/parent/dashboard', function () {
        return view('parent.dashboard');
    })->name('parent.dashboard');
});


Route::get('/migrate-seed', function () {
    try {
        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Migration dan seeding berhasil dijalankan.',
            'output' => Artisan::output(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});