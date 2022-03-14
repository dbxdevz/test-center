<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\StatisticController;
use App\Http\Controllers\Api\DoneVariantController;
use App\Models\DoneVariant;
use App\Models\User;

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
Route::post('/login', [AuthController::class, 'login']);

Route::get('/subjects', [SubjectController::class, 'index']);

Route::get('/schools', [SchoolController::class, 'index']);
Route::get('/schools/search', [SchoolController::class, 'search']);

Route::post('/register', [AuthController::class, 'register']);

/* Login user */
Route::prefix('')
    ->middleware(['auth:sanctum'])
    ->group(
        function () {
            // Profile
            Route::get('/profile', function (Request $request) {
                $user = User::where('id', auth('sanctum')->id())->with('school')->first();

                return response(['user' => $user], 200);
            });

            Route::get('/status', [DoneVariantController::class, 'status']);
            Route::get('/avg/{subject}', [DoneVariantController::class, 'avg']);

            Route::post('/logout', [AuthController::class, 'logout']);

            Route::post('/end-test', [QuestionController::class, 'endTest']);
            Route::post('/check-test', [QuestionController::class, 'checkTest']);

            Route::get('/subjects/{subject}/questions', [QuestionController::class, 'index']);

            Route::get('/ranking', [DoneVariantController::class, 'rank']);

            Route::get('/total-avg', [DoneVariantController::class, 'totalAvg']);
        }
    );
