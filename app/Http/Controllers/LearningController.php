<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\CourseStudent;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    public function index()
    {
        // Mengambil siswa yang saat ini login
        $user = Auth::user();

        // Query seluruh kelas yang dimiliki oleh siswa yang login
        $my_courses = $user->courses()->with('category')->orderBy('id', 'DESC')->get();

        foreach ($my_courses as $course) {
            // Menghitung jumlah pertanyaan dari suatu course
            $totalQuestionsCount = $course->questions()->count();

            // Menghitung jawaban dari suatu course yang telah dikerjakan oleh siswa
            $answeredQuestionsCount = StudentAnswer::where('user_id', $user->id)
                ->whereHas('question', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })->distinct()->count('course_question_id');


            if ($answeredQuestionsCount < $totalQuestionsCount) {
                $firstUnansweredQuestion = CourseQuestion::where('course_id', $course->id)
                    ->whereNotIn('id', function ($query) use ($user) {
                        $query->select('course_question_id')->from('student_answers')
                            ->where('user_id', $user->id);
                    })->orderBy('id', 'asc')->first();

                // Kalau udah dijawab 2 soal, maka mulai lagi ke soal 3,4,5..dst jika tiba" keluar. Kalau udah dijawab semua, berarti null. Soal selesai dikerjakan
                $course->nextQuestionId = $firstUnansweredQuestion ? $firstUnansweredQuestion->id : null;
            } else {
                $course->nextQuestionId = null;
            }
        }
        return view('students.courses.index', [
            'my_courses' => $my_courses,
        ]);
    }

    public function learning(Course $course, $question)
    {
        $user = Auth::user();

        // Check apakah siswa benar telah terdaftar pada course yang dipilih untuk dikerjakan
        $isEnrolled = $user->courses()->where('course_id', $course->id)->exists();

        if (!$isEnrolled) {
            abort(404);
        }

        $currentQuestion = CourseQuestion::where('course_id', $course->id)->where('id', $question)->firstOrFail();

        return view('students.courses.learning', [
            'course' => $course,
            'question' => $currentQuestion
        ]);
    }

    public function learning_rapport(Course $course)
    {

        $user_id = Auth::id(); // Sama saja dengan Auth::user()->id;
        $studentAnswers = StudentAnswer::with('question')
            ->whereHas('question', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })->where('user_id', $user_id)->get();

        $totalQuestion = CourseQuestion::where('course_id', $course->id)->count();
        $correctAnswersCount = $studentAnswers->where('answer', 'correct')->count();
        $passed = $correctAnswersCount == $totalQuestion;

        // dd($studentAnswers);
        return view('students.courses.learning_rapport', [
            'course' => $course,
            'studentAnswers' => $studentAnswers,
            'totalQuestion' => $totalQuestion,
            'correctAnswersCount' => $correctAnswersCount,
            'passed' => $passed
        ]);
    }

    public function learning_finished(Course $course)
    {
        return view("students.courses.learning_finished", [
            'course' => $course
        ]);
    }
}
