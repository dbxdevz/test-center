<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\AnswersUser;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function math()
    {
        $questions = Question::where('subject_id', 1)
            ->with('answers:id,answer,question_id')
            ->select('id', 'question', 'subject_id')
            ->get();

        return response(['questions' => $questions], 200);
    }

    public function endTest(Request $request)
    {
        $answers = $request->answers;
        $variant = $request->variant;

        foreach ($answers as $answer) {
            AnswersUser::createOrUpdate(['user_id' => 1, 'variant_id' => $variant], [
                'user_id' => 1,
                'answer_id' => $answer['answer'],
                'question_id' => $answer['question'],
                'variant_id' => $variant,
            ]);
        }

        return response(['message' => 'successfully saved answers'], 200);
    }

    public function checkTest(Request $request)
    {
        $data = [];
        $user = Auth::user();
        $variant = $request->variant; // 1
        $subject   = $request->subject;
        $questions = Question::where('variant_id', $variant)->where('subject_id', $subject)->with('correctAnswers')->get(); // questions from certain variant

        foreach ($questions as $question) {
            $answersUser = AnswersUser::where('user_id', Auth::user()->id)
                                        ->where('variant_id', $variant)
                                        ->where('question_id', $question->id)
                                        ->get();
            $data[] = [
                'question' => $question->question,
                'correct' => 0,
                'incorrect' => 0,
                'bal' => 0
            ];

            foreach ($answersUser as $answerUser) {
                if ($question->correctAnswers->contains(function ($value, $key) use ($answerUser) {
                    return $value->id == $answerUser->answer_id;
                })) {
                    end($data)['correct'] += 1;
                } else {
                    end($data)['incorrect'] += 1;
                }
            }

            $procent = end($data)['correct'] / count($question->correctAnswers) * 100;

            $bal = 0;
            if ($procent >= 50) {
                $bal = 1;
            }
            if (end($data)['incorrect'] = 0 && count($question->correctAnswers) > 1 && $procent == 100) {
                $bal = 2;
            }
            end($data)['bal'] = $bal;
        }

        return response(["answers" => $data], 200);
    }
}
