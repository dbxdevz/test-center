<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        return response(['schools' => School::select('id', 'name')->inRandomOrder()->limit(10)->get()], 200);
    }

    public function search(Request $request)
    {
        $schools = School::where('name', 'LIKE', '%'.$request->school.'%')->select('id', 'name')->get();

        return response(['school' => $schools], 200);
    }
}
