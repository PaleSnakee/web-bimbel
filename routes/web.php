<?php

use App\Http\Controllers\Admin\ClassRoomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LearningScheduleController;
use App\Http\Controllers\Admin\LearningSessionController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\StudentAttendanceController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Parent\ChildController as ParentChildController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\TryoutController as StudentTryoutController;
use App\Http\Controllers\Tutor\AttendanceController;
use App\Http\Controllers\Tutor\DashboardController as TutorDashboardController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TutorController;
use App\Http\Controllers\Admin\TryoutQuestionController as AdminQuestionController;
use App\Http\Controllers\Tutor\TryoutQuestionController as TutorQuestionController;
use App\Http\Controllers\Admin\TryoutController as AdminTryoutController;
use App\Http\Controllers\Admin\TryoutResultController as AdminTryoutResultController;
use App\Http\Controllers\Tutor\TryoutController as TutorTryoutController;
use App\Http\Controllers\Tutor\TryoutResultController as TutorTryoutResultController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


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
    Route::resource('tryouts.questions', AdminQuestionController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('tryouts/{tryout}/status', [AdminTryoutController::class, 'updateStatus'])
        ->name('tryouts.update-status');
    Route::get('student-attendances', [StudentAttendanceController::class, 'index'])
        ->name('student-attendances.index');
    Route::get('student-attendances/{learningSession}', [StudentAttendanceController::class, 'show'])
        ->name('student-attendances.show');
    Route::get('student-attendances/{learningSession}/edit', [StudentAttendanceController::class, 'edit'])
        ->name('student-attendances.edit');
    Route::get('tryout-results', [AdminTryoutResultController::class, 'index'])
        ->name('tryout-results.index');
    Route::put('student-attendances/{learningSession}', [StudentAttendanceController::class, 'update'])
        ->name('student-attendances.update');
});

Route::middleware(['auth', 'role:tutor'])->group(function () {
    Route::get('/tutor/dashboard', [TutorDashboardController::class, 'index'])->name('tutor.dashboard');
    Route::get('/tutor/attendances', [AttendanceController::class, 'index'])
        ->name('tutor.attendances.index');
    Route::get('/tutor/attendances/{learningSession}/edit', [AttendanceController::class, 'edit'])
        ->name('tutor.attendances.edit');
    Route::put('/tutor/attendances/{learningSession}', [AttendanceController::class, 'update'])
        ->name('tutor.attendances.update');
    Route::resource('/tutor/tryouts', TutorTryoutController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
        ->names('tutor.tryouts');
    Route::resource('/tutor/tryouts.questions', TutorQuestionController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names('tutor.tryouts.questions');
    Route::get('/tutor/tryout-results', [TutorTryoutResultController::class, 'index'])
        ->name('tutor.tryout-results.index');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    Route::get('tryouts', [StudentTryoutController::class, 'index'])->name('tryouts.index');
    Route::get('tryouts/{tryout}', [StudentTryoutController::class, 'show'])->name('tryouts.show');
    Route::post('tryouts/{tryout}/start', [StudentTryoutController::class, 'start'])->name('tryouts.start');
    Route::get('tryouts/{tryout}/attempts/{attempt}/result', [StudentTryoutController::class, 'result'])
        ->name('tryouts.attempts.result');
    Route::get('tryouts/{tryout}/attempts/{attempt}/review', [StudentTryoutController::class, 'review'])
        ->name('tryouts.attempts.review');
    Route::get('tryouts/{tryout}/attempts/{attempt}', [StudentTryoutController::class, 'attempt'])
        ->name('tryouts.attempts.show');
    Route::put('tryouts/{tryout}/attempts/{attempt}/answers', [StudentTryoutController::class, 'saveAnswer'])
        ->name('tryouts.attempts.answers.update');
    Route::post('tryouts/{tryout}/attempts/{attempt}/submit', [StudentTryoutController::class, 'submit'])
        ->name('tryouts.attempts.submit');
});

Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/children', [ParentChildController::class, 'index'])->name('children.index');
    Route::get('/children/{student}', [ParentChildController::class, 'show'])->name('children.show');
    Route::get('/tryouts', [ParentChildController::class, 'tryouts'])->name('tryouts.index');
    Route::get('/attendances', [ParentChildController::class, 'attendances'])->name('attendances.index');
});
