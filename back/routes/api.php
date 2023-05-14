<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\GateToGateController;
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
//Запросы  API SDK
//******************************************************************* */
Route::post('/sdk/cardtokenization', [ResultController::class, 'cardtokenization']);
Route::post('/sdk/pay', [ResultController::class, 'sdkpay']);

//Card saving api
Route::put('/card/add', [CardController::class, 'addcardwithrandompay']);
Route::put('/card/addzero', [CardController::class, 'addcardwithrandompay']);
Route::delete('/card/delete', [CardController::class, 'deletecard']);
Route::post('/card/list', [CardController::class, 'cardList']);
Route::post('/card/result', [CardController::class, 'result']);


//G2G payment
//**************************************************************** */
Route::post('/g2g/pay', [GateToGateController::class, 'g2gpay']);
Route::post('/g2g/result', [GateToGateController::class, 'result']);
Route::post('/g2g/result3ds', [GateToGateController::class, 'result3ds']);
Route::get('/g2g/checking3ds', [GateToGateController::class, 'checking3ds']);



Route::post('/test/pay', [PayController::class, 'put']);