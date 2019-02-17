<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageTestTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('address1', 50);
            $table->string('address2', 50)->nullable();
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();
            $table->string('country',2)->default('US');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('comments', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('person_id')->unsigned();
            $table->string('title');
            $table->text('text');
            $table->timestamps();
            $table->softDeletes();
        });
    }

}
