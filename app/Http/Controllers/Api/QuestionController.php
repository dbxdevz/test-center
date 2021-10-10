<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\AnswersUser;
use App\Models\Question;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $variant = Variant::inRandomOrder()->first();
        $questions = Question::where('subject_id', $request->subject)
            ->with('answers:id,answer,question_id')
            ->select('id', 'question')
            ->where('variant_id', $variant->id)
            ->get();

        return response(["questions" => $questions, "variant_id" => $variant->id], 200);
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
        $variant = $request->variant;
        $subject   = $request->subject;
        $questions = Question::where('variant_id', $variant)
            ->where('subject_id', $subject)
            ->with('correctAnswers')
            ->get();
        $total_bals = 0;
        foreach ($questions as $question) {
            $answersUser = AnswersUser::where('user_id', 1)
                                        ->where('variant_id', $variant)
                                        ->where('question_id', $question->id)
                                        ->get();
            $total_bals += $question->answers->count() > 5 ? 2 : 1;
            $data[] = [
                'question' => $question->id,
                'correct' => 0,
                'incorrect' => 0,
                'bal' => 0
            ];

            $last_key = array_key_last($data);

            foreach ($answersUser as $answerUser) {
                if ($question->correctAnswers->contains(function ($value) use ($answerUser) {
                    return $value->id == $answerUser->answer_id;
                })) {
                    $data[$last_key]['correct'] += 1;
                } else {
                    $data[$last_key]['incorrect'] += 1;
                }
            }

            $correct_procent = $data[$last_key]['correct'] / $question->correctAnswers->count() * 100;
            if ($data[$last_key]['incorrect'] == 0 && $correct_procent == 100) {
                $data[$last_key]['bal'] = 2;
            } elseif ($data[$last_key]['incorrect'] <= 1 && $correct_procent >= 50) {
                $data[$last_key]['bal'] = 1;
            }
        }

        $bals = array_sum(array_column($data, 'bal'));

        return response(['result' => $data, 'bals' =>$bals, 'total_bals' => $total_bals], 200);
    }
}
