<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function math()
    {
        $questions = Question
            ::where('subject_id', 1)
            ->with('answers:id,answer,question_id')
            ->select('id', 'question', 'subject_id')
            ->get();

        return response(['questions' => $questions], 200);
    }
}
