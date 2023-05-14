<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use Illuminate\Http\Request;
use App\Models\NewPayments;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public $public = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4aX50d5Smj4XNUDaJiqTZmL1zF8I0ylWy6nNVzZVkcuO6gxzxiiCfnWy97YS8meMmkG682Yf2GoOGropTMntfu8m2wzEOj+69sK4JpT/h7y7/1Ij+jaYU4Ax/i55eopEFhsO2gRKMn97nGTksDO2cQ+EeOsbp0QIVm119PiFpZYwKNak4u+uNkfUav3D1ks/cAgZfNhcKyEuYlbRx7O5DPQXqv/N0dU3jityFUJwjOJIdknu93BGQzEosQyfHCxrNsVqn+WRsM3/7B78wY+3atpoCVYNY018aKlILR7ZuTy5VuAXiSivwYingmpsh/3U4e4POcGalHpyJtmCtbPZvwIDAQAB';
     
    
    private function makeFlatParamsArray($arrParams, $parent_name = '')
            {
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



    
            public function incom(Request $req){

        $amount = $req->input('amount');
        $description = $req->input('description');

        $ans = DB::table('new_pay')
        -> insert([
            'amount'  => $amount,
            'description'  => $description,
        ]);

        return response($ans);
    }

    public function CurrentRequesrStatus(Request $req){

        $amount = $req->input('amount');
        $description = $req->input('description');

        // sleep(10);

        

        $ans = DB::table('new_pay')
        -> insert([
            'amount'  => $amount,
            'description'  => $description,
            
        ]);

        return response($ans);
    }

    // Функции обращения
    public function test(Request $req){
        return response()->json(['name' => 'Abigail', 'state' => 'CA']);
    }

    // Функции оплата на платежной странице
    public function pay(Request $req){

        $amount = $req->input('amount') ? $req->input('amount') : 10;
        $description = $req->input('description') ? $req->input('description') : 'Нет описание';
        $status = 'new';
        $savecard_id = $req->input('savecard') ? $req->input('savecard') : 0;
        $currency = $req->input('currency') ? $req->input('currency') : 'RUB'; // Добавить валидацию!
        $lc_salt = 'qwerty'; // Добавить валидацию!

        $ans = DB::table('new_pay')
        -> insertGetId([
            'pg_amount'  => $amount,
            'pg_description'  => $description,
            'pg_status'  => $status,
            'lc_savecard_id' => $savecard_id,
            'lc_salt' => $lc_salt,
            'pg_currency' => $currency,
        ]);

        $pg_merchant_id = '541637';
        $secret_key = 'i0soXJL1pPQayDSs';

        $request = $requestForSignature = [
        'pg_order_id' => $ans,
        'pg_merchant_id'=> $pg_merchant_id,
        'pg_amount' => $amount,
        'pg_description' => $description,
        'pg_salt' => $lc_salt,
        'pg_currency' => $currency,
        'pg_user_phone_text' => '+7 910 476-97-33',
        'pg_result_url' => 'https://2ff6-46-39-54-115.ngrok-free.app//api/result',
        'pg_request_method' => 'POST',
         // 'pg_success_url' => 'https://api.staging.paygame.ru/api/v1/payments/process/paybox/success/',
         //  'pg_failure_url' => 'https://api.staging.paygame.ru/api/v1/payments/process/paybox/failure/',
         // 'pg_success_url_method' => 'POST',
         // 'pg_failure_url_method' => 'POST',
         // 'pg_site_url' => 'https://staging.paygame.ru',
         //  'pg_lifetime' => '604800',
         //  'pg_user_contact_email' => 'web@a-gubarev.ru',
         // 'pg_user_ip' => '127.0.0.1',
         // 'pg_postpone_payment' => '0',
         'pg_language' => 'ru',
         'pg_testing_mode' => '1',
         'pg_user_id' => '466',
        //  'pg_receipt_positions' => [
        //     [
        //         // В случае формирования чеков в Республике Узбекистан, в параметре "name" необходимо передавать
        //         // дополнительные значения в определённой последовательности.
        //         // Детальную информацию можно найти в разделе "Особенности формирования фискальных чеков"
        //         'name' => 'название товара',
        //         'count' => '10',
        //         'tax_type' => '3',
        //         'price' => '900',
        //     ]
        //     ],

            // 'pg_receipt_positions[0][count]' => 1,
            // 'pg_receipt_positions[0][name]' => 'название товара 1',
            // 'pg_receipt_positions[0][tax_type]' => 0,
            // 'pg_receipt_positions[0][price]' => 4,
            // 'pg_receipt_positions[1][count]' => 1,
            // 'pg_receipt_positions[1][name]' => 'название товара 2',
            // 'pg_receipt_positions[1][tax_type]' => 3,
            // 'pg_receipt_positions[1][price]' => 5,  
        ];

        // Превращаем объект запроса в плоский массив
        $requestForSignature = $this->makeFlatParamsArray($requestForSignature);

        
        // Генерация подписи
        ksort($requestForSignature); // Сортировка по ключю
        array_unshift($requestForSignature, 'init_payment.php'); // Добавление в начало имени скрипта
        array_push($requestForSignature, $secret_key); // Добавление в конец секретного ключа
       
        $request['pg_sig'] = md5(implode(';', $requestForSignature)); // Полученная подпись
       
        $response = Http::asForm()->post('https://api.paybox.money/init_payment.php',$request);

        $xmlObject = simplexml_load_string($response);
        $url = (string)$xmlObject->pg_redirect_url;

        Storage::disk('local')->put('responce.txt', $response);

        return response()->
        json([
            'id' => $ans,
            'amount' => $req->input('amount'), 
            'test' => $req->input('test'),
            'test1' => $req->input('test1') ? $req->input('test1') : 'не задан',
            'paystatus' => 'created',
            // 'url' => 'https://customer.paybox.money/pay.html?customer=72a927320fda10f910edcdf02077e4e0'
            'url' => $url
        ]);
    }
    public function cardSave(Request $req){

        $pg_merchant_id = '541637';
        $secret_key = 'i0soXJL1pPQayDSs';

        $request = $requestForSignature = [
            'pg_merchant_id' => $pg_merchant_id,
            'pg_user_id'     => 123,
            'pg_post_link'   => 'http://paybox.money',
            'pg_back_link'   => 'http://paybox.money',
            'pg_salt'        => 'string'
        ];

        /**
         * Функция превращает многомерный массив в плоский
         */
        
           
            // Превращаем объект запроса в плоский массив
            $requestForSignature = $this->makeFlatParamsArray($requestForSignature);

            // Генерация подписи
            ksort($requestForSignature); // Сортировка по ключю
            array_unshift($requestForSignature, 'add'); // Добавление в начало имени скрипта
            array_push($requestForSignature, $secret_key); // Добавление в конец секретного ключа

            //echo '<pre>'; print_r($requestForSignature,);echo '</pre>';
            $request['pg_sig'] = md5(implode(';', $requestForSignature)); // Полученная подпись
            //echo '<pre>'; print_r($request);echo '</pre>'; die();
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.paybox.money/v1/merchant/541637/cardstorage/add',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $request,
            CURLINFO_HEADER_OUT => true,
            ));

            $response = curl_exec($curl);

            // $info = curl_getinfo($curl);
            // var_dump(implode(';', $requestForSignature), 'dsd');
            // var_dump($response, 'dsd');
            // die();
            // curl_close($curl);

            $xmlObject = simplexml_load_string($response);
            $url = (string)$xmlObject->pg_redirect_url;

            // dd($url);
            // die(var_dump($url));


        return response()->
        json([
            'amount' => $req->input('amount'), 
            'url' => $url,
        ]);
    }

    public function paystatus(Request $req){
        $ans = DB::table('new_pay')
        ->select(('*'))
        ->where('id', $req->input('id'))
        ->get();
        return response()->json($ans);
    }

    public function result(Request $req){

        $input = $req->collect();

        $pay = NewPayments::where('id',$req->input('pg_order_id'))->get();

        if ($pay!=[] && $pay[0]->pg_status =='success' ) 
            return response('Payment with id ' . $req->input('pg_order_id') . ' Already finished',409);
        //  return response($pay[0]->pg_status);

        $status = $req->input('pg_result') ? "success" : 'fail';



        $affected = DB::table('new_pay')
              ->where('id', $req->input('pg_order_id'))
              ->update([
                'pg_payment_id' => $req->input('pg_payment_id'),
                'pg_status' => $status
                ]);
            

        Storage::disk('local')->put('resp.txt', $input);
        return response($affected ? 'ok' : 'rejected');
        // return response($affected );
    }

    public function widgetresultreceiver(Request $req){

        $input = $req->collect();

        
        $pay = NewPayments::where('id',$input['order'])->get();

        // return response($pay[0]->pg_status);

        if ($pay!=[] && $pay[0]->pg_status =='success' ) 
            return response('Payment with id ' . $req->input('pg_order_id') . ' Already finished',409);
        //  return response($pay[0]->pg_status);

        $status = $input['status']['code'];
        // return response($status);


        $affected = DB::table('new_pay')
              ->where('id', $input['order'])
              ->update([
                'pg_payment_id' => $req->input('id'),
                'pg_status' => $status
                ]);
            

        Storage::disk('local')->put('resp.txt', $input);
        return response($affected ? 'ok' : 'rejected');
        // return response($affected );
    }

    public function last(Request $req){


        $pay = new NewPayments();
        $pay->save();

        // $ans = NewPayments::all()->last();


        return response($pay->id);
    }

    // sdk
    public function cardtokenization(Request $req){

        $array = [
            'type' => 'bank_card',
            'options' => [
              'card_number' => '4111111111111111',
              'card_holder_name' => 'NAME',
              'card_exp_month' => '12',
              'card_exp_year' => '24',
            ]
          ];
          $json = json_encode($array); // Переводим данные в json
        //   dd($json);
          $base64 = base64_encode($json); // Кодируем в Base64
        //   dd($base64);
          $chunks = str_split($base64, 200); // Разбиваем на части по 200 символов
          
        //   dd(strlen($chunks[0]));
          $result = [];

          $keyFinal = "-----BEGIN PUBLIC KEY-----\r\n" . chunk_split($this->public) . "-----END PUBLIC KEY-----";
          

          foreach ($chunks as $chunk) {
              openssl_public_encrypt($chunk, $encrypted, $keyFinal); // Шифруем публичным ключом
              $result[] = base64_encode($encrypted); // Снова кодируем в base64
          }
          
         $body = [
            'data'=> $result,
            'token'=> 'YZ3GDIAMV1qAxuwlUphbZ2l4hKVMzHRC'
         ];

        // dd($result);
        $response = Http::withHeaders([
            'Content-type' => 'application/json',
            'Request-Id' => '16'
        ])->post('https://api.paybox.money/v5/sdk/tokenize', $body);
        
        return response( $response);
    }


    //SDK
    public function sdkpay(Request $req){

        $input = $req->collect();

        $data = [
            "order_id"        => '577',
            "auto_clearing"  => 1,
            "amount"            => 10,
            "currency"            => "RUB",
            "description"            => "Описание заказа",
            "test"            => 0,
            "options"            => [
            //   "custom_params"            => [],
              "user"            => [
                "email"            => "yury.myworkmail@gmail.com",
                "phone"            => "+79104769733"
              ]
            ],
            "transaction"  => [
              "type"  => "tokenized_card",
            //   "type"  => "bank_card",
              "options"  => [
                "token"  => $input['token'],
                "card_cvv"  => $input['cvv']
                ]
            ]
        ];


        $json = json_encode($data); // Переводим данные в json
        // dd($json);
        $base64 = base64_encode($json); // Кодируем в Base64
        // dd($base64);
        $chunks = str_split($base64, 200); // Разбиваем на части по 200 символов
        // dd(strlen($chunks[0]));
        $result = [];

        $keyFinal = "-----BEGIN PUBLIC KEY-----\r\n" . chunk_split($this->public) . "-----END PUBLIC KEY-----";
        

        foreach ($chunks as $chunk) {
            openssl_public_encrypt($chunk, $encrypted, $keyFinal); // Шифруем публичным ключом
            $result[] = base64_encode($encrypted); // Снова кодируем в base64
        }
        
        $body = [
        'data'=>$result,
        'token'=> 'YZ3GDIAMV1qAxuwlUphbZ2l4hKVMzHRC'
        ] ;
       

        // dd($body['data']);
        // dd($body);
        $response = Http::withHeaders([
            'Content-type' => 'application/json',
            'Request-Id' => '18'
        ])->post('https://api.paybox.money/v5/sdk/charge', $body);


        return response( $response);
        // return response($data);
        
    }
    
    // card save
  


  

}


/*
{
    "pg_order_id":"100009",
    "pg_payment_id":"792244896",
    "pg_result":"1",
    "pg_salt":"6QcZ1CH2Ok0tOWFM",
    "pg_sig":"8efd040b77e1167c454deb49a29378d8",
    "pg_amount":"3",
    "pg_currency":"RUB",
    "pg_net_amount":"2.94",
    "pg_ps_amount":"3",
    "pg_ps_full_amount":"3",
    "pg_ps_currency":"RUB",
    "pg_description":"xxcc",
    "pg_payment_date":"2023-04-21 22:39:42",
    "pg_can_reject":"1",
    "pg_user_phone":"79104769733",
    "pg_need_phone_notification":"undefined",
    "pg_user_contact_email":"yury.myworkmail@gmail.com",
    "pg_need_email_notification":"1",
    "pg_payment_method":"bankcard",
    "pg_captured":"1",
    "pg_card_pan":"4111-11XX-XXXX-1111",
    "pg_card_exp":"12\/24",
    "pg_card_owner":"TEts",
    "pg_card_brand":"VI",
    "pg_payment_system":"undefined"
}

{
    "id": 798456301,
    "status": {
        "code": "success"
    },
    "order": "3434",
    "amount": "10.00",
    "refund_amount": "0.00",
    "currency": "RUB",
    "description": "Описание заказа",
    "expires_at": "2023-04-27T11:56:43Z",
    "created_at": "2023-04-26T17:56:43Z",
    "updated_at": "2023-04-26T17:57:05Z",
    "param1": null,
    "param2": null,
    "param3": null,
    "options": {
        "callbacks": {
            "result_url": "https://2ff6-46-39-54-115.ngrok-free.app//api/result"
        },
        "user": {
            "email": "user@test.com",
            "phone": "79104769733"
        },
        "receipt_positions": null
    },
    "salt": "CdUjHziLXJioLOd4",
    "sig": "a6becab724421759960281670c82b74d"
}

*/
