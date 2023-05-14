<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use Illuminate\Http\Request;
// use Illuminate\Http\Response;
use App\Models\NewPayments;
use App\views\check;
// use resources\views\chech;
use Illuminate\Support\Facades\DB;

class GateToGateController extends Controller
{
    //
    private function makeFlatParamsArray($arrParams, $parent_name = ''){
        $arrFlatParams = [];
        $i = 0;
        foreach ($arrParams as $key => $val) {
            $i++;
            /**
             * Имя делаем вида tag001subtag001
             * Чтобы можно было потом нормально отсортировать и вложенные узлы не запутались при сортировке
             */
            $name = $parent_name . $key . sprintf('%03d', $i);
            if (is_array($val)) {
                $arrFlatParams = array_merge($arrFlatParams, $this->makeFlatParamsArray($val, $name));
                continue;
            }
            $arrFlatParams += array($name => (string)$val);
        }

        return $arrFlatParams;
    }

      //G2G
    public function g2gpay(Request $req){

        $amount = $req->input('amount') ? $req->input('amount') : 10;
        $description = $req->input('description') ? $req->input('description') : 'Нет описание';
        $status = 'new';
        $savecard_id = $req->input('savecard') ? $req->input('savecard') : 0;
        $currency = $req->input('currency') ? $req->input('currency') : 'RUB'; // Добавить валидацию!
        $lc_salt = 'qwerty'; // Добавить валидацию!


        $pg_merchant_id = $req->input('merchant_id') ? $req->input('merchant_id') : '541637';
        $secret_key = 'i0soXJL1pPQayDSs';


        $input = $req->all();
        return response()->json($input);

        $ans = DB::table('new_pay')
        -> insertGetId([
            'type'             => 'g2gpayment',
            'pg_amount'        => $amount,
            'pg_description'   => $description,
            'pg_status'        => $status,
            'lc_savecard_id'   => $savecard_id,
            'lc_salt'          => $lc_salt,
            'pg_currency'      => $currency,
        ]);

        


        $request = $requestForSignature = [
            'pg_amount' => $amount,
            'pg_currency' => $currency,
            'pg_description' => $description,
            'pg_merchant_id'=> $pg_merchant_id,
            'pg_order_id' => $ans,

            'pg_user_id'   => 'test0001',
            'pg_user_email' => 'test@testbench.com',
            'pg_user_phone' => '79104769733',
            'pg_user_ip'    => '185.102.131.54',
            
            'pg_card_name'  => 'YURIY',
            'pg_card_pan'   => 2200150543546300,
            'pg_card_cvc'   => 243,
            'pg_card_month' => '05',
            'pg_card_year'  => 29,
            
            'pg_salt'       => $lc_salt,

            'pg_auto_clearing' => 0,
            'pg_testing_mode' => 0,
            'pg_result_url' => 'https://416b-46-39-54-23.ngrok-free.app/api/result'

        ];
    
            
        // Превращаем объект запроса в плоский массив
        $requestForSignature = $this->makeFlatParamsArray($requestForSignature);

        
        // Генерация подписи
        ksort($requestForSignature); // Сортировка по ключю
        array_unshift($requestForSignature, 'payment'); // Добавление в начало имени скрипта
        array_push($requestForSignature, $secret_key); // Добавление в конец секретного ключа
        
        $request['pg_sig'] = md5(implode(';', $requestForSignature)); // Полученная подпись
        

        // dd($request);
        // dd($requestForSignature);

        $g2gresponse = Http::asForm()->post('https://api.paybox.money/g2g/payment',$request);
        Storage::disk('local')->put('g2gResponce.txt', $g2gresponse);

        $xmlObject = simplexml_load_string($g2gresponse);

        if ($xmlObject->pg_3ds!=1){

            return response('ok');

        };
        
        $md = $xmlObject->pg_3d_md;
        $acsurl = $xmlObject->pg_3d_acsurl;
        $pareq = $xmlObject->pg_3d_pareq;

        $TermUrl =  'https://416b-46-39-54-23.ngrok-free.app/api/g2g/result3ds';   
            
        $data = [
            'MD'=> $md,
            'PaReq'=> $pareq[0],
            'TermUrl' => $TermUrl,
            // '$acsurl' =>$acsurl,
        ];
        return response()->json([
            'url' => 'https://416b-46-39-54-23.ngrok-free.app/api/g2g/checking3ds'.'?id='.$ans,
        ]);
        // dd($xmlObject);
        // return response()->json($xmlObject);
        // return view(asset($g2gresponse));
        
        // $data3dsresponse = Http::asForm()->post($acsurl,$data);
        // Storage::disk('local')->put('$data3dsresponse.txt', $data3dsresponse);

        // return response()->json([
        //     'status' => 'ok', 
        //     'url' => "https://416b-46-39-54-23.ngrok-free.app/api/g2g/checking3ds"
        // ]);     
    }

    public function result(Request $res){
        $input = $res->all;

        return response()->json($input);

    }

    public function result3ds(Request $req){

        $input = $req->all();

        Storage::disk('local')->put('3DSresponce.txt', implode('|', $input));
        // Storage::disk('local')->re

        return response('sds');
    }

    public function checking3ds(Request $req){
        // return ('Hellqsao World'); 
        // return view('welcome', ['name' => 'James']);
        // $id = uniqid();
        $id = $req->input('id');;
        return view('check', array('name' => 'Taylor', 'id'=>$id));
    }
}
