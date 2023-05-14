<?php



namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use App\Models\NewPayments;

// require_once dirname(__FILE__).'/flatarray.php';

class CardController extends Controller
{
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

    private $pg_merchant_id = '541637';
    private $secret_key = 'i0soXJL1pPQayDSs';

    public function addcardwithrandompay(Request $req){

        $request = $requestForSignature = [
            'pg_merchant_id'=> $this->pg_merchant_id,
            'pg_user_id' => 100,
            'pg_post_link'=>'https://2ff6-46-39-54-115.ngrok-free.app//api/card/result',
            'pg_back_link'=>'https://test.ru',
            'pg_salt'=>'abcde',
        ];   
         // Превращаем объект запроса в плоский массив
         $requestForSignature = $this->makeFlatParamsArray($requestForSignature);

        
         // Генерация подписи
         ksort($requestForSignature); // Сортировка по ключю
         array_unshift($requestForSignature, 'add'); // Добавление в начало имени скрипта
         array_push($requestForSignature, $this->secret_key); // Добавление в конец секретного ключа
        
         $request['pg_sig'] = md5(implode(';', $requestForSignature)); // Полученная подпись
        
         $response = Http::asForm()->post('https://api.paybox.money/v1/merchant/'.$this->pg_merchant_id.'/cardstorage/add',$request);
 
         $xmlObject = simplexml_load_string($response);
         $url = (string)$xmlObject->pg_redirect_url;
 
         Storage::disk('local')->put('save_card_with_pay.txt', $response);
  
//add;https://test.ru;541637;https://2ff6-46-39-54-115.ngrok-free.app//api/card/result;abcde;100;i0soXJL1pPQayDSs
//add;https://test.ru;541637;https://2ff6-46-39-54-115.ngrok-free.app//api/card/result;abcde;100;i0soXJL1pPQayDSs

        return response($url);
    }
    public function addcardwithzeropay(Request $req){

        return response('addcardwithzeropay');
    }
    public function deletecard(Request $req){

        return response('deletecard');
    }
    public function cardList(Request $req){

        return response('cardList');
    }
    public function result(Request $req){

        Storage::disk('local')->put('save_card_result.txt',  json_encode( $req->input() ));

        return response(json_encode( $req->input() ));
    }
}
