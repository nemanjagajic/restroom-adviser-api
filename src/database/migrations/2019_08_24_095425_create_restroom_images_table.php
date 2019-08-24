<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestroomImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restroom_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('restroom_id');
            $table->string('path');
            $table->timestamps();

            $table->foreign('restroom_id')
                ->references('id')->on('restrooms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restroom_images');
    }
}
