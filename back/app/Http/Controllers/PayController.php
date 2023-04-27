<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pay;
use App\Models\NewPayments;
use Illuminate\Support\Facades\Http;

class PayController extends Controller
{

    public function put(Request $req){

        $amount = $req->input('amount') ? $req->input('amount') : 10;
        $description = $req->input('description') ? $req->input('description') : 'Нет описание';
        $status = 'new';
        $savecard_id = $req->input('savecard') ? $req->input('savecard') : 0;
        $currency = $req->input('currency') ? $req->input('currency') : 'RUB'; // Добавить валидацию!
        $lc_salt = 'qwerty'; // Добавить валидацию!
        // sleep(5);

        // $ans = DB::table('new_pay')
        // -> insertGetId([
        //     'pg_amount'  => $amount,
        //     'pg_description'  => $description,
        //     'pg_status'  => $status,
        //     'lc_savecard_id' => $savecard_id,
        //     'lc_salt' => $lc_salt,
        //     'pg_currency' => $currency,
        // ]);


        // $flights = Flight::where('active', 1)
        //        ->orderBy('name')
        //        ->take(10)
        //        ->get();

        $pay = new Pay();
        $pay -> pg_amount = 10;
        $pay -> pg_description = 'Test payments';

        $pay -> save();

        // $ans = $pay->latest()->first();

        // $ans = $pay->where('id','100001')->get();
        $ans = $response = Http::get('https://jsonplaceholder.typicode.com/posts');

        return response($ans);

    }

    
}
