<?php

use Mockery\Undefined;

const PI = 3.14159;
const API_KEY = "your-api-key";
const no_data = 'nodata';
const secret_key = 'i0soXJL1pPQayDSs';

const okxml = '
    <?xml version="1.0" encoding="UTF-8"?>
    <response>
        <pg_status>ok</pg_status>
        <pg_payment_id>4567788</pg_payment_id>
        <pg_redirect_url>https://api.paybox.money/pay.html?customer=498333170d6a895148c57c53ffb18287</pg_redirect_url>
        <pg_redirect_url_type>need data</pg_redirect_url_type>
        <pg_salt>bdwLavL9lg6It91b</pg_salt>
        <pg_sig>709633e91387c56ac6fb7cb33d1e07d8</pg_sig>
    </response>';

const wrongValidationXML_9403 = '
    <?xml version="1.0" encoding="utf-8"?>
    <response>
        <pg_status>error</pg_status>
        <pg_error_code>9403</pg_error_code>
        <pg_error_description>Неверная валидация</pg_error_description>
    </response>';

const wrongShopNumberXML_9997 = '
    <?xml version="1.0" encoding="utf-8"?>
    <response>
        <pg_status>error</pg_status>
        <pg_error_code>9997</pg_error_code>
        <pg_error_description>Неверный номер магазина</pg_error_description>
    </response>';

const wrongSigXML_9998 = '
    <?xml version="1.0" encoding="utf-8"?>
    <response>
        <pg_status>error</pg_status>
        <pg_error_code>9998</pg_error_code>
        <pg_error_description>Некорректная подпись запроса</pg_error_description>
    </response>';





    function validation($request){

        $errors = init();

        //Check the headers of request. Should be a multipart/form-dat
        headerValidation($request,$errors);
        

        /*
        Checking the base params for payments page request
        For this request it is:
        pg_amount                    - amount of pay. Should be a number        
        pg_description               - description of pay
        pg_merchant_id               - id of shop in Payment System
        pg_salt                      - some random string
        pg_sig                       - signature
        */
        // dd($errors);
        identifierValidation($request,$errors);
        // dd($errors);
        amountValidation($request,$errors);
        // dd($errors);
        descriptionValidation($request,$errors);
        // dd($errors);
        saltValidation($request,$errors);
        // dd($errors);
        signatureValidation($request,$errors);
        
        // dd($errors);
        // Сollect the response to the request by the error code
        if ($errors->status == "ok") $errors->response = okxml;
        else{
            switch ($errors->data[0]['errorCode']) {
                case 9403:
                    $errors->response = wrongValidationXML_9403;
                    break;
                case 9997:
                    $errors->response = wrongShopNumberXML_9997;
                    break;
                case 9998:
                    $errors->response = wrongSigXML_9998;
                    break;
            }
        }

        return $errors;
    }

    function validationNoHeader($request){

        $errors = init();      

        /*
        Checking the base params for payments page request
        For this request it is:
        pg_amount                    - amount of pay. Should be a number        
        pg_description               - description of pay
        pg_merchant_id               - id of shop in Payment System
        pg_salt                      - some random string
        pg_sig                       - signature
        */
        // dd($errors);
        identifierValidation_purephp($request,$errors);
        // dd($errors);
        amountValidation_purephp($request,$errors);
        // dd($errors);
        descriptionValidation_purephp($request,$errors);
        // dd($errors);
        saltValidation_purephp($request,$errors);
        // dd($errors);
        signatureValidation_purephp($request,$errors);
        
        // dd($errors);
        // Сollect the response to the request by the error code
        if ($errors->status == "ok") $errors->response = okxml;
        else{
            switch ($errors->data[0]['errorCode']) {
                case 9403:
                    $errors->response = wrongValidationXML_9403;
                    break;
                case 9997:
                    $errors->response = wrongShopNumberXML_9997;
                    break;
                case 9998:
                    $errors->response = wrongSigXML_9998;
                    break;
            }
        }

        return $errors;
    }

    //Making a base data structure of error analyzation
    function init(){
        return  $errors = (object) [
            'status'=>'ok',
            'data'=>[],    
        ];
    }

    //Making a function that make a flat array from provided data structure
     function makeFlatParamsArray($arrParams, $parent_name = ''){
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
                $arrFlatParams = array_merge($arrFlatParams, makeFlatParamsArray($val, $name));
                continue;
            }
            $arrFlatParams += array($name => (string)$val);
        }

        return $arrFlatParams;
    }

    function headerValidation($request, $errors){

        $contentHeader = $request->header('content-type');
    
        if (!str_contains($contentHeader,'multipart/form-data')){
    
            $errors->status='error';
            $errors->data[] = [
                            'errorDesc' => 'Wrong context type',
                            'errorCode' => 9998,
                        ];     
        }
    }

    function identifierValidation($request,$errors){
        if ($request->missing(['pg_merchant_id'])) {
            $errors->status='error';
            $errors->data[] = [
                        'errorDesc' => 'Shop identifier is missing, please check parametr pg_merchant_id',
                        'errorCode' => 9403,
            ];
        }
    };

    function amountValidation($request,$errors){
 
        if ($request->missing(['pg_amount'])) {
            $errors->status='error';
            $errors->data[] = [
                            'errorDesc' => 'Missing amount in request',
                            'errorCode' => 9403
                        ];
        } else {
            if ( (float)$request->input('pg_amount') != $request->input('pg_amount')) {
                $errors->status='error';
             
                if(preg_match("/^[\d]+(?:,[\d]+)?$/", $request->input('pg_amount'))){
                    $errors->data[] = [
                        'errorDesc' => 'Amount should be a number, but pg_amount = '
                                        .$request->input('pg_amount')
                                        .' it is '
                                        .gettype($request->input('pg_amount'))
                                        .'.'
                                        .' Please replace "," to "."',
                        'errorCode' => 9403
                    ];
                } 

                else {
                    $errors->data[] = [
                        'errorDesc' => 'Amount should be a number, but pg_amount = '
                                    .$request->input('pg_amount')
                                    .' it is '
                                    .gettype($request->input('pg_amount')),
                        'errorCode' => 9403
                    ];
                }

            };
        };
    };

    function descriptionValidation($request,$errors){
        if ($request->missing(['pg_description'])) {
            $errors->status='error';
            $errors->data[] = [
                        'errorDesc' => 'Description is missing, please check parametr pg_description',
                        'errorCode' => 9403,
            ];
        }
    };

    function saltValidation($request,$errors){
        if ($request->missing(['pg_salt'])) {
            $errors->status='error';
            $errors->data[] = [
                        'errorDesc' => 'Salt is missing, please check parametr pg_salt',
                        'errorCode' => 9403,
            ];
        }
    };

    function signatureValidation($request,$errors){

        // dd($request);

        if ($request->missing(['pg_sig'])) {
            $errors->status='error';
            $errors->data[] = [
                        'errorDesc' => 'Sig is missing, please check parametr pg_sig',
                        'errorCode' => 9998,
            ];
           
        }
        else 
        {
            // $url = $request->url();
            $url = 'init_payment.php';

            $data = [];

            $lastPart = '';

            if (preg_match('/[^\/]+\/?$/', $url, $matches)) {
                $lastPart = $matches[0];
            }
            
            // dd($lastPart);

            $secret_key = 'i0soXJL1pPQayDSs';
            $requestForSignature = $request->all();
            $sigFromRequest = $requestForSignature['pg_sig'];
            unset($requestForSignature['pg_sig']);

            // dd( $requestForSignature );

            $requestForSignature = $params = makeFlatParamsArray($requestForSignature);
            ksort($requestForSignature); // Сортировка по ключю
            array_unshift($requestForSignature, $lastPart); // Добавление в начало имени скрипта
            array_push($requestForSignature, $secret_key); // Добавление в конец секретного ключа
            $sig = md5(implode(';', $requestForSignature)); // Полученная подпись

            // dd( implode(';', $requestForSignature));
            if ($sigFromRequest!=$sig) {
                $errors->status='error';
                $data[] = [
                            'errorDesc' => 'Sig is wrong',
                            'errorCode' => 9998,
                ];
            }
            

            $requestForSignaturePGonly = [];

            foreach ($requestForSignature as $key => $value) {
              if (strpos($key, 'pg_') === 0) {
                // $result .= $value;
                $requestForSignaturePGonly[]= $value;
              }
            }

            // dd( $params);
            // dd($requestForSignaturePGonly, $requestForSignature);
            // dd(count($requestForSignaturePGonly)!=count($requestForSignature));

            if (count($requestForSignaturePGonly)!=count($params)) {
                $requestForSignaturePGonly = makeFlatParamsArray($requestForSignaturePGonly);
                ksort($requestForSignaturePGonly); // Сортировка по ключю
                array_unshift($requestForSignaturePGonly, $lastPart); // Добавление в начало имени скрипта
                array_push($requestForSignaturePGonly, $secret_key); // Добавление в конец секретного ключа
                $sigPGonly = md5(implode(';', $requestForSignaturePGonly)); // Полученная подпись
    
                if ($sigFromRequest==$sigPGonly) {
                    
                    $data[] = [
                                'errorDesc' => 'Custom parametrs not counted in signature',
                                'errorCode' => 9998,
                    ];
                    
                }
            }
        }
    };


    function identifierValidation_purephp($request,$errors){
        if (!isset($request['pg_merchant_id'])  )  {
            $errors->status='error';
            $errors->data[] = [
                        'errorDesc' => 'Shop identifier is missing, please check parametr pg_merchant_id',
                        'errorCode' => 9403,
            ];
        }
    };

    function amountValidation_purephp($request,$errors){
 
        if (!isset($request['pg_amount'])) {
            $errors->status='error';
            $errors->data[] = [
                            'errorDesc' => 'Missing amount in request',
                            'errorCode' => 9403
                        ];
        } else {
            if ( (float)$request['pg_amount'] != $request['pg_amount']) {
                $errors->status='error';
             
                if(preg_match("/^[\d]+(?:,[\d]+)?$/", $request['pg_amount'])){
                    $errors->data[] = [
                        'errorDesc' => 'Amount should be a number, but pg_amount = '
                                        .$request['pg_amount']
                                        .' it is '
                                        .gettype($request['pg_amount'])
                                        .'.'
                                        .' Please replace "," to "."',
                        'errorCode' => 9403
                    ];
                } 

                else {
                    $errors->data[] = [
                        'errorDesc' => 'Amount should be a number, but pg_amount = '
                                    .$request['pg_amount']
                                    .' it is '
                                    .gettype($request['pg_amount']),
                        'errorCode' => 9403
                    ];
                }

            };
        };
    };

    function descriptionValidation_purephp($request,$errors){
        if (!isset($request['pg_description'])) {
            $errors->status='error';
            $errors->data[] = [
                        'errorDesc' => 'Description is missing, please check parametr pg_description',
                        'errorCode' => 9403,
            ];
        }
    };

    function saltValidation_purephp($request,$errors){
        if (!isset($request['pg_salt'])) {
            $errors->status='error';
            $errors->data[] = [
                        'errorDesc' => 'Salt is missing, please check parametr pg_salt',
                        'errorCode' => 9403,
            ];
        }
    };

    function signatureValidation_purephp($request,$errors){

        // dd($request);

        if (!isset($request['pg_sig'])) {
            $errors->status='error';
            $errors->data[] = [
                        'errorDesc' => 'Sig is missing, please check parametr pg_sig',
                        'errorCode' => 9998,
            ];
           
        }
        else 
        {
            // $url = $request->url();
            $url = 'init_payment.php';

            $data = [];

            $lastPart = '';

            if (preg_match('/[^\/]+\/?$/', $url, $matches)) {
                $lastPart = $matches[0];
            }
            
            // dd($lastPart);

            $secret_key = 'i0soXJL1pPQayDSs';
            $requestForSignature = $request;
            $sigFromRequest = $requestForSignature['pg_sig'];
            unset($requestForSignature['pg_sig']);

            // dd( $requestForSignature );

            $requestForSignature = $params = makeFlatParamsArray($requestForSignature);
            ksort($requestForSignature); // Сортировка по ключю
            array_unshift($requestForSignature, $lastPart); // Добавление в начало имени скрипта
            array_push($requestForSignature, $secret_key); // Добавление в конец секретного ключа
            $sig = md5(implode(';', $requestForSignature)); // Полученная подпись

            // dd( implode(';', $requestForSignature));
            // dd( $sig , $sigFromRequest);
            if ($sigFromRequest!=$sig) {
                $errors->status='error';
                $errors->$data[] = [
                            'errorDesc' => 'Sig is wrong',
                            'errorCode' => 9998,
                ];
            }
            

            $requestForSignaturePGonly = [];

            foreach ($requestForSignature as $key => $value) {
              if (strpos($key, 'pg_') === 0) {
                // $result .= $value;
                $requestForSignaturePGonly[]= $value;
              }
            }

            // dd( $params);
            // dd($requestForSignaturePGonly, $requestForSignature);
            // dd(count($requestForSignaturePGonly)!=count($requestForSignature));

            if (count($requestForSignaturePGonly)!=count($params)) {
                $requestForSignaturePGonly = makeFlatParamsArray($requestForSignaturePGonly);
                ksort($requestForSignaturePGonly); // Сортировка по ключю
                array_unshift($requestForSignaturePGonly, $lastPart); // Добавление в начало имени скрипта
                array_push($requestForSignaturePGonly, $secret_key); // Добавление в конец секретного ключа
                $sigPGonly = md5(implode(';', $requestForSignaturePGonly)); // Полученная подпись
    
                if ($sigFromRequest==$sigPGonly) {
                    
                    $data[] = [
                                'errorDesc' => 'Custom parametrs not counted in signature',
                                'errorCode' => 9998,
                    ];
                    
                }
            }
        }
    };
?>