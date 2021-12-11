<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Statistic;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public function math()
    {
        $statistic = Statistic::where('user_id', auth('sanctum')->id)->where('type', '')->get();
    }
}
