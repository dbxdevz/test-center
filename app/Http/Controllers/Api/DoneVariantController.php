<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoneVariant;
use App\Models\Question;
use App\Models\Subject;
use App\Models\SubjectUser;
use Illuminate\Support\Facades\DB;

class DoneVariantController extends Controller
{
    public function status()
    {

        $userSubjects = SubjectUser::where('user_id', auth('sanctum')->id())->with('subjects')->pluck('subject_id');

        $subjects = Subject::whereIn('id', $userSubjects)->select('id', 'subject')->get();


        $data = [];

        foreach($subjects as $subject){
            $doneVariants = DoneVariant::where('user_id', auth('sanctum')->id())
            ->where('subject_id', $subject->id)->get()->count();


            $variantNum = Question::where('subject_id', $subject->id)->groupBy('variant_id')->select('variant_id')->get()->count();

            if($variantNum == 0){
                $result = 0;
            }else{
                $result = ($doneVariants / $variantNum) * 100;
            }

            $data[] = [
                'subject' => $subject->subject,
                'result' => $result
            ];
        }

        return response(['data' => $data], 200);
    }
}
