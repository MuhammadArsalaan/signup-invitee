<?php

use App\Http\Controllers\AssesmentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => 'auth:sanctum'], function(){
    //All secure URL's
    Route::post('update/{id}', [UserController::class, 'updateUser']);
});

Route::post('sendInvitation', [UserController::class, 'sendInvitation']);
Route::get('register/{email}', [UserController::class, 'userInvitationMessage']);
Route::post('register/{email}', [UserController::class, 'registerUser']);
Route::get('verifypin/{email}', [UserController::class, 'getVerifyPin']);
Route::post('verifypin/{email}', [UserController::class, 'verifyPin']);
Route::post('login', [UserController::class, 'login']);