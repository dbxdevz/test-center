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
        $questions = Question
            ::where('subject_id', 1)
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
            AnswersUser::create([
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
        $variant = $request->variant;
        $data = [];
        $questions = Question::where('variant_id', $variant)->get();

        foreach ($questions as $question) {

            $correctAnswers = Answer::where('question_id', $question->id)->where('correct', true)->get();

            $oneCorrectAnswers = $correctAnswers;
            $twoCorrectAnswers = $correctAnswers;

            if (count($oneCorrectAnswers) >= 2) {
                foreach ($oneCorrectAnswers as $correctAnswer) {
                    $oneCorrectAnswers = $oneCorrectAnswers->except([$correctAnswer->id]);
                }
            }

            if(count($twoCorrectAnswers) == 1){
                foreach ($twoCorrectAnswers as $correctAnswer) {
                    $twoCorrectAnswers = $twoCorrectAnswers->except($correctAnswer->id);
                }
            }


            if ($oneCorrectAnswers) {
                foreach ($oneCorrectAnswers as $correctAnswer) {
                    $answersUser = AnswersUser::where('user_id', 1)->where('variant_id', $variant)->where('question_id', $correctAnswer->question_id)->first();
                    if ($answersUser->answer_id == $correctAnswer->id) {
                        $data [] = [
                            'question' => $correctAnswer->question_id,
                            'answer' => true,
                        ];
                    } else {
                        $data[] = [
                            'question' => $answersUser->question_id,
                            'answer' => false,
                        ];
                    }
                }
            }
            if ($twoCorrectAnswers){
                foreach ($twoCorrectAnswers as $correctAnswer){
                    $answersUser = AnswersUser::where('user_id', 1)->where('variant_id', $variant)->where('question_id', $correctAnswer->question_id)->get();
                }
                $data [] = [
                    'question' => '2 answer',
                    'answer' => true
                ];
            }
        }


        return response(["answers" => $data], 200);
    }
}
