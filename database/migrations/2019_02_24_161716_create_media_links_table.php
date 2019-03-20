<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_manager_attachments', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('library_id')->unsigned()->nullable();
            $table->integer('of_id')->nullable();
            $table->string('of_type')->nullable();
            $table->text('meta')->nullable(); // { role: , reason: }
            $table->boolean('owner')->default(false);

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
        Schema::dropIfExists('file_manager_attachments');
    }
}
