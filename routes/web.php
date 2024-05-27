<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseQuestionController;
use App\Http\Controllers\CourseStudentController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentAnswerController;
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

    Route::prefix('dashboard')->name('dashboard.')->group(function () {

        // TEACHER -----------

        // Membuat Course baru
        Route::resource('courses', CourseController::class)
            ->middleware('role:teacher'); // Route hanya bisa diakses oleh role teacher

        // Route guru membuat pertanyaan baru
        Route::get('/course/question/create/{course}', [CourseQuestionController::class, 'create'])
            ->middleware('role:teacher')
            ->name('course.create.question');

        // Route guru menyimpan pertanyaan
        Route::post('/course/question/save/{course}', [CourseQuestionController::class, 'store'])
            ->middleware('role:teacher')
            ->name('course.create.question.store');

        Route::resource('course_question', CourseQuestionController::class)
            ->middleware('role:teacher');

        // Menampilkan seluruh siswa yang mendaftar di suatu kelas
        Route::get('/course/students/show/{course}', [CourseStudentController::class, 'index'])
            ->middleware('role:teacher')
            ->name('course.course_students.index');

        // Menampilkan halaman untuk menambah siswa di suatu kelas
        Route::get('/course/students/create/{course}', [CourseStudentController::class, 'create'])
            ->middleware('role:teacher')
            ->name('course.course_students.create');

        Route::post('/course/students/create/save/{course}', [CourseStudentController::class, 'store'])
            ->middleware('role:teacher')
            ->name('course.course_students.store');

        // STUDENT -------

        // Menampilkan halaman finished ketika siswa selesai mengerjakan
        Route::get('/learning/finished/{course}', [LearningController::class, 'learning_finished'])
            ->middleware('role:student')
            ->name('learning.finished.course');

        // Menampilkan raport nilai perolehan siswa
        Route::get('learning/rapport/{course}', [LearningController::class, 'learning_rapport'])
            ->middleware('role:student')
            ->name('learning.rapport.course');

        // Menampilkan beberapa kelas yang telah diberikan oleh guru
        Route::get('/learning', [LearningController::class, 'index'])
            ->middleware('role:student')
            ->name('learning.index'); // Route hanya bisa diakses oleh role student

        // Menampilkan soal dari course
        Route::get('/learning/{course}/{question}', [LearningController::class, 'learning'])
            ->middleware('role:student')
            ->name('learning.course');

        // Menyimpan jawaban dari pertanyaan
        Route::post('/learning/{course}/{question}', [StudentAnswerController::class, 'store'])
            ->middleware('role:student')
            ->name('learning.course.answer.store');
    });
});

require __DIR__ . '/auth.php';
