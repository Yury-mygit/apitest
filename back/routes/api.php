<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\PayController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/greeting', [ResultController::class, 'incom']);
Route::post('/add', [ResultController::class, 'add']);


Route::get('/test', [ResultController::class, 'test']);

//******************************************************************* */
//Запросы в платежную системы
//******************************************************************* */
//Запрос на проведение платежа
Route::post('/pay', [ResultController::class, 'pay']);
//Запрос на сохранение карты
Route::post('/cardsave', [ResultController::class, 'cardSave']);

//******************************************************************* */
//Точка входа для платежной системы
//******************************************************************* */
Route::post('/result', [ResultController::class, 'result']);
Route::post('/wresult', [ResultController::class, 'widgetresultreceiver']);
Route::get('/last', [ResultController::class, 'last']);

//******************************************************************* */
//Запросы на бек за статусом
//******************************************************************* */
Route::get('/paystatus', [ResultController::class, 'paystatus']);

//******************************************************************* */
//Запросы  
//******************************************************************* */


Route::post('/test/pay', [PayController::class, 'put']);