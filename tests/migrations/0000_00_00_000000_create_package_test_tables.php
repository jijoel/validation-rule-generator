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
        Schema::dropIfExists('people');
        Schema::dropIfExists('comments');

        Schema::create('people', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('address1', 50);
            $table->string('address2', 50)->nullable();
            $table->string('country',2)->default('US');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('comments', function(Blueprint $table)
        {

            $table->increments('id');
            // We can only have one per table
            // $table->bigIncrements('big_id');
            // $table->mediumIncrements('med_id');
            // $table->smallIncrements('small_id');
            // $table->tinyIncrements('tiny_id');

            $table->integer('person_id')->unsigned();
            $table->decimal('lat', 10, 6)->nullable();

            $table->decimal('amount_dec', 8, 2);
            $table->unsignedDecimal('amount_ud', 8, 2);
            $table->double('amount_dbl', 8, 2);
            $table->float('amount_flt', 8, 2);

            $table->integer('votes_i');
            $table->bigInteger('votes_bi');
            $table->smallInteger('votes_si');
            $table->mediumInteger('votes_mi');
            $table->tinyInteger('votes_ti');

            $table->unsignedInteger('votes_ui');
            $table->unsignedBigInteger('votes_ubi');
            $table->unsignedMediumInteger('votes_umi');
            $table->unsignedSmallInteger('votes_usi');
            $table->unsignedTinyInteger('votes_uti');

            $table->char('name_c', 50);
            $table->string('name_s', 50);
            $table->string('name_s2');
            $table->text('description_t')->nullable();
            $table->longText('description_lt');
            $table->mediumText('description_mt');
            // $table->lineString('positions_ls');        // sqlite not supported
            // $table->multiLineString('positions_mls');  // sqlite not supported
            $table->json('options_json');
            $table->jsonb('options_jsonb');

            $table->uuid('uuid')->nullable();
            $table->binary('data')->nullable();
            $table->boolean('confirmed');
            // $table->enum('level', ['easy', 'hard']);
            $table->rememberToken();
            $table->ipAddress('visitor');
            $table->macAddress('device');
            $table->morphs('taggable');
            $table->nullableMorphs('taggable_nm');

            // $table->geometry('positions_geo');
            // $table->geometryCollection('positions_geoc');
            // $table->multiPoint('positions_mtp');
            // $table->multiPolygon('positions_mty');
            // $table->point('position_pt');
            // $table->polygon('positions_poly');

            $table->time('sunrise_t');
            $table->timeTz('sunrise_ts');
            $table->year('birth_year');
            $table->date('happened_at');
            $table->dateTime('happened_at_dt');
            $table->dateTimeTz('happened_at_dtz');

            $table->timestamp('added_on');
            $table->timestampTz('added_on_tz');
            $table->timestamps();
            // $table->nullableTimestamps();
            // $table->timestampsTz();
            $table->softDeletes();
            // $table->softDeletesTz();
        });
    }

}
