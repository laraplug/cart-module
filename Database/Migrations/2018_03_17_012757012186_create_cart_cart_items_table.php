<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart__cart_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('shop_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('session_id');
            $table->string('instance');

            $table->integer('product_id')->unsigned();
            $table->integer('price')->unsigned();
            $table->integer('quantity')->unsigned();
            $table->text('options')->nullable();
            $table->string('note', 500)->nullable();

            $table->timestamps();
        });

        Schema::create('cart__cart_item_options', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('cart_item_id')->unsigned();
            $table->integer('product_option_id')->unsigned();
            $table->text('value');

            $table->timestamps();

            $table->foreign('cart_item_id')->references('id')->on('cart__cart_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart__cart_item_options');
        Schema::dropIfExists('cart__cart_items');
    }
}
