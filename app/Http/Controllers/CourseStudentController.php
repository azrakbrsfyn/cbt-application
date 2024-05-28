<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\CourseStudent;
use App\Models\StudentAnswer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Course $course)
    {

        $students = $course->students()->orderBy('id', 'desc')->get();
        $questions = $course->questions()->orderBy('id', 'desc')->get();
        $totalQuestion = $questions->count();

        foreach ($students as $student) {
            $studentAnswers = StudentAnswer::whereHas('question', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })->where('user_id', $student->id)->get();

            $answerCount = $studentAnswers->count();
            $correctAnswersCount = $studentAnswers->where('answer', 'correct')->count();

            if ($answerCount == 0) {
                $student->status = 'Not Started Yet';
            } elseif ($correctAnswersCount < $totalQuestion) {
                $student->status = "Not Passed";
            } elseif ($correctAnswersCount == $totalQuestion) {
                $student->status = 'Passed';
            }
        }

        return view("admin.students.index", [
            'course' => $course,
            'students' => $students,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course)
    {
        //
        $students = $course->students()->orderBy('id', 'DESC')->get();
        return view('admin.students.add_student', [
            'course' => $course,
            'students' => $students
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Course $course)
    {
        //
        // Validasi data
        $validated = $request->validate([
            'email' => 'required|string'
        ]);

        // Apakah email student sesuai dan ada? Kalau tidak ada, lempar pesan error email student tidak ditemukan
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $error = ValidationException::withMessages([
                'system_error' => ['Email student tidak ditemukan! Masukkan email yang telah terdaftar student'],
            ]);

            throw $error;
        }

        // Apakah email student sudah terdaftar? Kalau sudah, lempar pesan error email student sudah terdaftar
        $isEnrolled = $course->students()->where('user_id', $user->id)->exists();
        if ($isEnrolled) {
            $error = ValidationException::withMessages([
                'system_error' => ['Email student sudah terdaftar course ini!'],
            ]);

            throw $error;
        }

        DB::beginTransaction();
        try {
            $course->students()->attach($user->id);
            DB::commit();
            return redirect()->route('dashboard.course.course_students.index', $course);
        } catch (\Exception $e) {
            DB::rollBack();
            $error = ValidationException::withMessages([
                'system_error' => ['System Error!' . $e->getMessage()],
            ]);

            throw $error;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CourseStudent $courseStudent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CourseStudent $courseStudent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseStudent $courseStudent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseStudent $courseStudent)
    {
        //
    }
}
