<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTestTables extends Migration 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('foo');
        Schema::create('foo', function(Blueprint $table) 
        {
            $table->increments('id');
            $table->integer('oid')->unsigned()->unique();
            $table->string('code',4)->unique();
            $table->string('text1',20);
            $table->string('text2',20)->nullable();
            $table->index('text1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('foo');
    }

}
