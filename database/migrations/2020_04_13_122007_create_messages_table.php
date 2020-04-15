<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('laravel-messenger.table'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("sender_id");      //user or other sender
            $table->string("sender_type");    //the Model Class of associated sender
            $table->unsignedBigInteger("receiver_id")->nullable();                  //null refers to broadcast to everyone
            $table->string("receiver_type");
            $table->text("message")->nullable();
            $table->text("attachments")->nullable();    //json field contains attachments urls
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('laravel-messenger.table'));
    }
}
