<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    // Setiap course, itu memiliki category dari tabel categories
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Ngasih tau ke table course, bahwa eh kamu punya banyak pertanyaan di tabel course_question untuk setiap course
    public function questions()
    {
        return $this->hasMany(CourseQuestion::class, 'course_id', 'id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_students', 'course_id', 'user_id'); // 'course_students' adalah table pivot dari hubungan table courses dan users yang memiliki hubungan many to many
    }
}
