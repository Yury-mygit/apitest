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
        $id = $this->saveDataToDB($request);
        
        // $host = $request->host();
        // $httpHost = $request->httpHost();
        // $schemeAndHttpHost = $request->schemeAndHttpHost();
        // $uri = $request->path();   

        // dd( $host , $httpHost, $schemeAndHttpHost, $uri);
    
        $paramsAll = $request->all();

        // $paramsWithoutPG = $this->getParamsWithoutPG($request);
        // $paramsWithPG = array_filter($paramsAll, function($key) {
        //     return substr($key, 0, 3) === 'pg_';
        // }, ARRAY_FILTER_USE_KEY);


        // dd(json_decode(json_encode($paramsWithoutPG)) );

        // $headers = $request->header();

        $errorsGlobal = [];
        
        $ans = ''; 

        
        // Проверка, правильно ли задан заголовок
        $pos =  headerControll($request);
        if (count($pos)!=0) {
            if ($ans==''){
                $ans = wrongShopNumberXML_9998;
            }
            $errorsGlobal = [...$errorsGlobal, ...$pos];
        }
        
        // Проверка, есть ли все обязательные параметры
        $pos =  requiredParamsValidation( $request );
        if (count($pos)!=0) {
            if ($ans==''){
                $ans = wrongValidationXML_9403;
            }
            $errorsGlobal = [...$errorsGlobal, ...$pos];
        }

        //Signature check
        $paramsForSignature = $paramsAll;

        if ($request->missing(['pg_salt'])) {
            array_push ($errorsGlobal, 'pg_salt отсуствует');
        } else {
            $data = $request->All();
            unset($data['pg_sig']);
            ksort($data);
            array_unshift($data, 'init_payment');
            array_push($data, secret_key);
            $sig=md5(implode(';', $data));
            // dd($sig);
        }

        unset($paramsForSignature['pg_sig']);

        $attempt = Attempt::find($id);

        $attempt->error_desc = json_encode($errorsGlobal);

        $attempt->save();


        if (count($errorsGlobal)!=0){
            return response($ans)
            ->header('Content-Type', 'text/xml');
        }

        // $ans = str_replace('<<status>>', $pos['status'], okxml);
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



