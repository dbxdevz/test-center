<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt($data)) {
            return response('Credentials not match', 401);
        }

        Auth::user()->session_id = Session::getId();

        Auth::user()->save();

        return response([
            'token' => Auth::user()->createToken('API Token')->plainTextToken
        ], 200);
    }

    public function logout()
    {
        Auth::user()->session_id = null;

        Auth::user()->save();

        auth()->user()->tokens()->delete();

        return response(['message' => 'Tokens Revoked'], 200);
    }
}
