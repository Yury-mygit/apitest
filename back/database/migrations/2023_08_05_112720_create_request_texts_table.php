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
        Schema::create('request_texts', function (Blueprint $table) {
            $table->id();

            $table->string('xml_response',1000)->nullable(true); 
            $table->string('check_request',1800)->nullable(true); 
            $table->string('result_request',1800)->nullable(true);
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
        Schema::dropIfExists('request_texts');
    }
};
