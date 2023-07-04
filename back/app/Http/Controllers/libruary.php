<?php
const PI = 3.14159;
const API_KEY = "your-api-key";
const no_data = 'nodata';
const secret_key = 'dgr3erf4DF';

const okxml = 
    '<?xml version="1.0" encoding="UTF-8"?>
    <response>
        <pg_status>ok</pg_status>
        <pg_payment_id>4567788</pg_payment_id>
        <pg_redirect_url>https://api.paybox.money/pay.html?customer=498333170d6a895148c57c53ffb18287</pg_redirect_url>
        <pg_redirect_url_type>need data</pg_redirect_url_type>
        <pg_salt>bdwLavL9lg6It91b</pg_salt>
        <pg_sig>709633e91387c56ac6fb7cb33d1e07d8</pg_sig>
    </response>';

const wrongValidationXML_9403='
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

const wrongShopNumberXML_9998 = '
    <?xml version="1.0" encoding="utf-8"?>
    <response>
        <pg_status>error</pg_status>
        <pg_error_code>9998</pg_error_code>
        <pg_error_description>Некорректная подпись запроса</pg_error_description>
    </response>';


     function headerControll($request){
        $contentHeader = $request->header('content-type');

        $pos = str_contains($contentHeader,'multipart/form-data');

        if (!$pos){
            return ['Wrong context type'];
        }

        return [];
    }

     function requiredParamsValidation($request){

        $errors = [];

        if ($request->missing(['pg_amount'])) {
            array_push ($errors, 'pg_amount отсуствует');
        } else {
            if ( (float)$request->input('pg_amount') != $request->input('pg_amount')) {
                array_push ($errors, 'pg_amount должно быть числом');
            };
        };

        if ($request->missing(['pg_description'])) {
            array_push ($errors, 'pg_description is missing');
        }

        if ($request->missing(['pg_salt'])) {
            array_push ($errors, 'pg_salt отсуствует');
        }

        if ($request->missing(['pg_sig'])) {
            array_push ($errors, 'pg_sig отсуствует');
        }

        // $input = $request->all();

        
        return $errors;
    }

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

$fer ='vd';
?>