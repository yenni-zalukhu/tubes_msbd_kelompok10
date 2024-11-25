<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->increments('transaction_detail_id'); // Primary Key
            $table->unsignedInteger('transaction_id'); // Foreign Key
            $table->unsignedInteger('id'); // Foreign Key
            $table->integer('quantity'); // Jumlah produk
            $table->decimal('unit_price', 10, 2); // Harga per unit
            $table->timestamps(); // Created_at dan updated_at

            // Tambahkan foreign key ke tabel transaction
            $table->foreign('transaction_id')->references('transaction_id')->on('transaction')->onDelete('cascade');

            // Tambahkan foreign key ke tabel products
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_details');
    }
}
