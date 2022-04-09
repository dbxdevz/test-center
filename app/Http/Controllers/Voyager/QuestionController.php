<?php

namespace App\Http\Controllers\Voyager;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use Illuminate\Http\Request;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class QuestionController extends VoyagerBaseController
{
    public function store(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        // проверка кол-ства правильных ответов
        // должен быть хотя бы один правильный ответ
        $isAtLeastOneCorrect = false;
        for ($i = (int)$request->member - 1; $i >= 0; $i--) {
            $check = 'answer' . $i;
            if($request->$check){
                $isAtLeastOneCorrect = true;
                break;
            }
        }

        if (!$isAtLeastOneCorrect) {
            return redirect()->back();
        }
        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();
        $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

        for ($i = (int)$request->member - 1; $i >= 0; $i--) {
            $mem = 'member' . $i;
            $check = 'answer' . $i;
            if($request->$check){
                Answer::create(['answer' => $request->$mem, 'question_id' => $data->id, 'correct' => true]);
            }else{
                Answer::create(['answer' => $request->$mem, 'question_id' => $data->id, 'correct' => false]);
            }
        }

        event(new BreadDataAdded($dataType, $data));

        if (!$request->has('_tagging')) {
            if (auth()->user()->can('browse', $data)) {
                $redirect = redirect()->route("voyager.{$dataType->slug}.index");
            } else {
                $redirect = redirect()->back();
            }

            return $redirect->with([
                'message'    => __('voyager::generic.successfully_added_new')." {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
        } else {
            return response()->json(['success' => true, 'data' => $data]);
        }
    }
}
