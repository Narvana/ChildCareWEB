<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\Users\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UsersAccessController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware'=>['api'],'prefix'=>'auth'],
function()
{

    Route::get('/Test',[TestController::class,'Test']);

    Route::post('/Admin/Register',[UsersAccessController::class,'AdminRegister']);

    Route::post('/Admin/Login',[UsersAccessController::class,'AdminLogin']);
});

Route::group(['middleware'=>['CheckBearerToken'], 'prefix'=>'admin'],
function()
{

    Route::get('/Test/Token',[TestController::class,'Test']);

    Route::post('/Add/ProAct',[ContentController::class,'addProAct']);

    Route::get('/Get/ProAct',[ContentController::class,'getProAct']);

    Route::post('/Update/ProAct',[ContentController::class,'editProAct']);

    Route::post('/Add/Environment',[ContentController::class,'addEnvironment']);

    Route::get('/Get/Envirnoment',[ContentController::class,'getEnvironment']);

    Route::post('/Update/Environment',[ContentController::class,'editEnvironment']);

});