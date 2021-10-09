<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\SubjectController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Auth */
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

Route::post('/end-test', [QuestionController::class, 'endTest']);
Route::post('/check-test', [QuestionController::class, 'checkTest']);
Route::get("/subjects", [SubjectController::class, 'index']);
Route::get('/{subject}/questions', [QuestionController::class, 'index']);

/* Login user */
Route::prefix('test')
    ->middleware(['auth:sanctum', 'checksinglesession'])
    ->group(
        function () {
        // Profile
        Route::get('/profile', function (Request $request) {
            return $request->user();
        });
        // Question math
        Route::get('/math', [QuestionController::class, 'math']);
    }
    );
