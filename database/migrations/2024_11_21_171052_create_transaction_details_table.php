<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id('transaction_detail_id'); // Kolom transaction_detail_id (primary key dengan auto-increment)
            $table->unsignedBigInteger('transaction_id'); // Kolom transaction_id (foreign key ke transactions)
            $table->unsignedBigInteger('product_id'); // Kolom product_id (foreign key ke products)
            $table->integer('quantity'); // Kolom quantity
            $table->decimal('unit_price', 10, 2); // Kolom unit_price dengan 10 digit dan 2 angka desimal
            $table->timestamps(); // Kolom created_at dan updated_at

            // Menambahkan foreign key constraint
            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_details');
    }
}
