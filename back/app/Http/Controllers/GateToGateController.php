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


        // $input = $req->all();
        // return response()->json($input);

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
            'pg_result_url' => 'https://8c98-46-39-54-110.ngrok-free.app/api/result'

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

        $TermUrl =  'https://8c98-46-39-54-110.ngrok-free.app/api/g2g/result3ds';   
            
        $data = [
            'MD'=> $md,
            'PaReq'=> $pareq[0],
            'TermUrl' => $TermUrl,
            // '$acsurl' =>$acsurl,
        ];
        return response()->json([
            'url' => 'https://8c98-46-39-54-110.ngrok-free.app/api/g2g/checking3ds'.'?id='.$ans,
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

    public function paystart(Request $req){

        $input = $req->all();
        $secret_key = $input['secret_key'];

        unset($input['secret_key']);

        $request = $requestForSignature = $input;

        $requestForSignature = $this->makeFlatParamsArray($requestForSignature);

        // dd('sdss');
        
        // Генерация подписи
        ksort($requestForSignature); // Сортировка по ключю
        array_unshift($requestForSignature, 'payment'); // Добавление в начало имени скрипта
        array_push($requestForSignature, $secret_key); // Добавление в конец секретного ключа
        
        $request['pg_sig'] = md5(implode(';', $requestForSignature)); // Полученная подпись
        

        $g2gresponse = Http::asForm()->post('https://api.paybox.money/g2g/payment',$request);
        Storage::disk('local')->put('g2gResponce.txt', $g2gresponse);

        $str = $g2gresponse->body();
        $str = str_replace('"', '\"', $str);


        $xmlObject = simplexml_load_string($g2gresponse);

        $responseData = json_decode(json_encode((array)$xmlObject), TRUE);

        // dd($responseData);

        if ($responseData['pg_status']=='error') {
            $data = [ 'xml'=>$str, 'pg_status'=> $responseData['pg_status']   ];
            return response()->json($data );
        }

        // dd($responseData);

        $ans = DB::table('new_pay')
        -> insertGetId([
            'type'             => 'g2gpayment',
            'pg_amount'        => $input['pg_amount'],
            'pg_description'   => $input['pg_description'],
            'pg_salt'          => $input['pg_salt'],
            'pg_3ds'           => $responseData['pg_3ds'] ?? false,
            'pg_3d_acsurl'     => $responseData['pg_3d_acsurl'] ?? 'no data',
            'pg_3d_pareq'      => $responseData['pg_3d_pareq'] ?? 'no data',
            'pg_payment_id'    => $responseData['pg_payment_id'] ,
            
        ]);

        // dd($responseData);
      

        if ($responseData['pg_3ds']==0){

            return response()->json($responseData);

        };

           
        $server = env('SERVER');
        
        $TermUrl =  $server.'/api/g2g/result3ds/'.$ans;

        NewPayments::where('id', $ans) ->update(['TermUrl' => $TermUrl]);
        

        if (is_array($responseData['pg_3d_md']) && empty( $responseData['pg_3d_md'] )) $responseData['pg_3d_md'] = '';
               
        $responseData['TermUrl'] = $TermUrl;
        $responseData['paument_number'] = $ans;
        $responseData['xml'] = $str;

      
       

        return response()->json($responseData );
        

    }
    public function test(Request $req){

        $input = $req->all();

        return response()->json($input);

       

    }

  

  

    public function perform3ds(Request $req){
        // return ('Hellqsao World'); 
        // return view('welcome', ['name' => 'James']);
        // $id = uniqid();

        $id = NewPayments::where('id', $req->input('id'))->get();

        // dd($id);
        
        $pg_3d_acsurl = $id[0]->pg_3d_acsurl;
        $pg_3d_md = $id[0]->pg_3d_md;
        // $pg_3d_md = 'sdsd';
        $pg_3d_pareq = $id[0]->pg_3d_pareq;
        $TermUrl = $id[0]->TermUrl;

        return view('check', array( 
            'pg_3d_acsurl' =>$pg_3d_acsurl,
            'pg_3d_md'     =>$pg_3d_md,
            'pg_3d_pareq'  =>$pg_3d_pareq,
            'TermUrl'      =>$TermUrl,
        ));
    }

    public function result3ds(Request $req, string $id){

        $input = $req->all();

        // $rec = NewPayments::where('id', $id)->get();

        NewPayments::where('id', $id) ->update([
            'pg_3d_pares' => $input['PaRes'],
            'pg_3d_md' => $input['MD'],
        ]);

        $flattened = $input;
        array_walk($flattened, function(&$value, $key) {
            $value = "{$key}:{$value}";
        });
        

        Storage::disk('local')->put('3DSresponce.txt',  implode(', ', $flattened));
        // Storage::disk('local')->re

        return response('ok');
    }


    public function pares(Request $req) {

        $id = $req->input('id');

        $data = NewPayments::where('id', $id)->get();
        $pg_3d_pares = $data[0]->pg_3d_pares;

        if ($pg_3d_pares==Null) return response()->json(['pares'=>'no data']);

        return response()->json(['pg_3d_pares'=>$pg_3d_pares]);
    }

    public function payafter3ds(Request $req) {
        $id = $req->input('id');
        $secret_key = $req->input('secret_key');
        $pg_merchant_id = $req->input('pg_merchant_id');
    
        $data = NewPayments::where('id', $id)->get()->first();
    
        if (!$data) {
            return response()->json(['xml' => '', 'status' => 'error', 'message' => 'Payment with required id not found']);
        }
    
        $request = [
            'pg_3d_md' => $data->pg_3d_md ? $data->pg_3d_md : '',
            'pg_pares' => $data->pg_3d_pares,
            'pg_payment_id' => $data->pg_payment_id,
            'pg_merchant_id' => $pg_merchant_id,
            'pg_salt' => $data->pg_salt,
        ];
    
        $requestForSignature = $request;
        $requestForSignature = $this->makeFlatParamsArray($requestForSignature);
    
        ksort($requestForSignature); // Сортировка по ключю
        array_unshift($requestForSignature, 'paymentAcs'); // Добавление в начало имени скрипта
        array_push($requestForSignature, $secret_key); // Добавление в конец секретного ключа
    
        $request['pg_sig'] = md5(implode(';', $requestForSignature)); // Полученная подпись
    
        $response = Http::asForm()->post('https://api.paybox.money/g2g/paymentAcs', $request);
    
       

        $xml = $response->body();
        // dd( $xml);
        // $xml = str_replace('"', '\"', $xml);
        $xml = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $data = json_decode($json, true);
    
        $params = [
            'pg_payment_id' => isset($data['pg_payment_id']) ? $data['pg_payment_id'] : 'not data',
            'pg_amount' => isset($data['pg_amount']) ? $data['pg_amount'] : 'not data',
            'pg_clearing_amount' => isset($data['pg_clearing_amount']) ? $data['pg_clearing_amount'] : 'not data',
            'pg_status' => isset($data['pg_status']) ? $data['pg_status'] : 'not data',
            'pg_salt' => isset($data['pg_salt']) ? $data['pg_salt'] : 'not data',
            'pg_sig' => isset($data['pg_sig']) ? $data['pg_sig'] : 'not data',
            'pg_status_clearing' => isset($data['pg_status_clearing']) ? $data['pg_status_clearing'] : 'not data',
            'pg_datetime' => isset($data['pg_datetime']) ? $data['pg_datetime'] : 'not data',
            'pg_error_code' => isset($data['pg_error_code']) ? $data['pg_error_code'] : 'not data',
            'pg_error_description' => isset($data['pg_error_description']) ? $data['pg_error_description'] : 'not data',
        ];
    
        return response()->json(['xml' => $xml, 'status' => 'ok'] + $params);
    }
    
}


 // paymentAcs;;541637;ewogICJ0aHJlZURTU2VydmVyVHJhbnNJRCIgOiAiODQ2YzA4NTQtMjliYS00OWMxLTkwNzgtY2M4ZGE0MTI0NzIyIiwKICAibWVzc2FnZVR5cGUiIDogIkNSZXMiLAogICJtZXNzYWdlVmVyc2lvbiIgOiAiMi4xLjAiLAogICJhY3NUcmFuc0lEIiA6ICJiNzk1YWEyMi01NWI0LTQ5NDAtYWIzMC1iMDg3M2I4MjRiNGEiLAogICJjaGFsbGVuZ2VDb21wbGV0aW9uSW5kIiA6ICJZIiwKICAidHJhbnNTdGF0dXMiIDogIlkiCn0;888888888;abcde;i0soXJL1pPQayDSs
        // 41d3b9b1f662888cbd7069896decce41