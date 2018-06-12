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
            $table->json('option_values');
            $table->string('note', 500)->nullable();

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
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('cart__cart_items');

        Schema::enableForeignKeyConstraints();
    }
}
