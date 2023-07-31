<?php

namespace App\Http\Controllers;

require 'libruary.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Attempt;

class APIhelpController extends Controller
{

    // Функции обращения
    public function initPayments(Request $request)
    {
        // $id = $this->saveDataToDB($request);
    
        // $paramsAll = $request->all();

        // dd($id);

        
        $errorsGlobal = [];
        
        $ans = validation($request); 

        // dd($ans);
        
        // // Проверка, правильно ли задан заголовок
        // $errorInHeader =  headerControll($request);
        
        
        // // Проверка, есть ли все обязательные параметры
        // $errorInRequiredParams =  requiredParamsValidation( $request );
        
       

   
        $ans = okxml;

        return response($ans)
        ->header('Content-Type', 'text/xml');
    }

    public function getPayments(Request $request){
        // $attempts = Attempt::latest()->take(10)->get();
        $skip = $request->skip ? $request->skip : 0;
        $take  = $request->take ? $request->take : 10;

        // dd($skip, $take);
        // $attempts = Attempt::skip($skip)->take($take)->get();
        $attempts = Attempt::select('id','error_desc')->latest()->skip($skip)->take($take)->get();
        return response()->json($attempts);
    }


    public function saveDataToDB($request){
        $attempt = new Attempt;

        $attempt->pg_amount = $request->pg_amount || no_data;
        $attempt->pg_description = $request->pg_description || no_data;
        $attempt->pg_merchant_id = $request->pg_merchant_id || no_data;
        $attempt->pg_salt = $request->pg_salt || no_data;
        $attempt->pg_sig = $request->pg_sig || no_data;
        $attempt->user_params = json_encode($this->getParamsWithoutPG($request));
        $attempt->host   = $request->httpHost();

        $attempt->save();
        return $attempt->id;
    }

    public function getParamsWithoutPG($request){
        return array_filter($request->all(), function($key) {
            return substr($key, 0, 3) !== 'pg_';
          }, ARRAY_FILTER_USE_KEY);
    }
    public function getParamsWithPG($request){
        return array_filter($request->all(), function($key) {
            return substr($key, 0, 3) === 'pg_';
          }, ARRAY_FILTER_USE_KEY);
    }
}



