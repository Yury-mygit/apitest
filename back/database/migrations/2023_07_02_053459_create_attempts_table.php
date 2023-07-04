<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attempts', function (Blueprint $table) {
           
            // Идентификатор платежа
            $table->id()->from(100000000);

            // Обязательные параметры
            $table->float ('pg_amount')->nullable(true);
            $table->float ('pg_merchant_id')->nullable(true);
            $table->string('pg_description')->nullable(true);
            $table->string('pg_salt')->nullable(true);
            $table->string('pg_sig')->nullable(true);

            // Дополнительные параметры
            $table->string('pg_currency')->nullable(true);
            $table->string('pg_status')->nullable(true);
            $table->string('pg_check_url')->nullable(true);
            $table->string('pg_result_url')->nullable(true);
            $table->string('pg_request_method')->nullable(true);
            $table->string('pg_success_url')->nullable(true);                 
	        $table->string('pg_failure_url')->nullable(true);                  
	        $table->string('pg_success_url_method')->nullable(true);                  
	        $table->string('pg_failure_url_method')->nullable(true);                  
	        $table->string('pg_state_url')->nullable(true);                  
	        $table->string('pg_state_url_method')->nullable(true);                  
	        $table->string('pg_site_url')->nullable(true);                  
	        $table->string('pg_payment_system')->nullable(true);                  
	        $table->integer('pg_lifetime')->nullable(true);                  
	        $table->string('pg_user_phone')->nullable(true);                  
	        $table->string('pg_user_contact_email')->nullable(true);                
	        $table->string('pg_user_ip')->nullable(true);                  
	        $table->string('pg_postpone_payment')->nullable(true);                  
	        $table->string('pg_language')->nullable(true);                  
	        $table->boolean('pg_testing_mode')->nullable(true);                  
	        $table->string('pg_user_id')->nullable(true);                  
	        $table->integer('pg_recurring_start')->nullable(true);                  
	        $table->integer('pg_recurring_lifetime')->nullable(true);                  
	        $table->string('pg_receipt_positions_id')->nullable(true); 
	        $table->boolean('pg_3ds')->nullable(true); 
	        $table->string('pg_3d_acsurl')->nullable(true); 
	        $table->string('pg_3d_pareq',2000)->nullable(true); 
	        $table->string('pg_3d_pares',2000)->nullable(true); 
	        
	        $table->string('pg_3d_md')->nullable(true); 
	        $table->string('TermUrl')->nullable(true); 

            // Пользовательские параметры
            $table->string('user_params',2000)->nullable(true);
            
            //Описание ошибки инициации, если есть
            $table->string('error_desc',2000)->nullable(true); 
            $table->integer('error_number')->nullable(true); 
            $table->string('host')->nullable(true); 



            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attempts');
    }
};
