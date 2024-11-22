<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_detail', function (Blueprint $table) {
            $table->increments('shipping_id'); // Primary Key
            $table->unsignedBigInteger('transaction_id'); // Foreign Key
            $table->string('shipping_address', 255); // Alamat pengiriman
            $table->string('city', 100); // Kota
            $table->string('postal_code', 20); // Kode pos
            $table->dateTime('shipping_date'); // Tanggal pengiriman
            $table->dateTime('estimated_delivery'); // Estimasi pengiriman
            $table->enum('status', ['In progress', 'Shipped', 'Delivered', 'Cancelled']); // Status
            $table->timestamps(); // Created_at dan updated_at

            // Tambahkan foreign key ke tabel transaction
            $table->foreign('transaction_id')->references('transaction_id')->on('transaction')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_detail');
    }
}
