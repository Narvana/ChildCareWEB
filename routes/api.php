<?php

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

