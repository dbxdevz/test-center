<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update(UserUpdateRequest $request)
    {
        $data = $request->validated();

        $user = User::where('id', auth('sanctum')->id())->first();

        $user->update($data);

        return response(['message' => 'User updated successfully'], 200);
    }
}
