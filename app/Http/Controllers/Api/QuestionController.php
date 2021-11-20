<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\AnswersUser;
use App\Models\Question;
use App\Models\Result;
use App\Models\Statistic;
use App\Models\Subject;
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
        $subject = $request->subject;
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

            $total_bals += ($question->bal) ? 1 : 2;

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
            if ($data[$last_key]['bal'] == 2 && $question->bal == 1) {
                $data[$last_key]['bal'] = 1;
            }
        }

        $bals = array_sum(array_column($data, 'bal'));

        $result = Result::where('user_id', 1)
            ->where('subject_id', $subject)
            ->where('variant_id', $variant)
            ->first();
        if (!$result) {
            $typeSubject = Subject::where('id', $subject)->first();
            if($typeSubject->first){
                $subjectType = "first";
            }else if($typeSubject->second){
                $subjectType = "second";
            }else{
                $subjectType = "third";
            }
            $percent = (($bals / $total_bals) * 100) + 3; // 3 -> attempts
            Result::create([
                'user_id' => 1,
                'subject_id' => $subject,
                'variant_id' => $variant,
                'data' => json_encode($data),
                'bal' => $bals,
                'total_bals' => $total_bals,
                'attempts' => 2,
                'percent' => $percent,
                'type' => $subjectType,
            ]);
        }else{
            if ($result->attempts > 0) {
                $percent = ($bals / $total_bals * 100) + $result->attempts;
                $attempt = $result->attempts;

                if($result->percent <= $percent){
                    $result->update([
                        'data' => $data,
                        'bal' => $bals,
                        'total_bals' => $total_bals,
                        'percent' => $percent
                    ]);
                }

                $result->attempts = $attempt - 1;
                $result->save();
            } else {
                return response([
                    'message' => 'This attempt is not counted',
                    'result' => $data,
                    'bals' => $bals,
                    'total_bals' => $total_bals
                ], 200);
            }
        }

        $subject = Subject::where('id', $subject)->first();
        if($subject->first){
            $subject = "first";
        }else if($subject->second){
            $subject = "second";
        }else{
            $subject = "third";
        }
        $statPercent = 0;

        $percents = Result::where('user_id', 1)->where('type', $subject)->select('id','percent')->get();
        foreach ($percents as $percent){
            $statPercent += $percent->percent;
        }

        $counter = $percents->count();
        $bronze = (int)($counter / 5);
        $silver = (int)($bronze / 10);
        $gold = (int)($silver / 5);
        $bronze -= $silver * 10;
        $silver -= $gold * 5;

        Statistic::updateOrCreate(['user_id' => 1, 'type' => $subject], [
            'percent' => $statPercent,
            'bronze' => $bronze,
            'silver' => $silver,
            'gold' => $gold,
        ]);

        return response(['result' => $data, 'bals' => $bals, 'total_bals' => $total_bals], 200);
    }
}
