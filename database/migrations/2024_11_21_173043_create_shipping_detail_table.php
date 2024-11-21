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
            $table->id('shipping_id'); // Kolom shipping_id sebagai primary key dengan auto-increment
            $table->unsignedBigInteger('transaction_id'); // Kolom transaction_id sebagai foreign key ke transactions
            $table->string('shipping_address', 255); // Kolom shipping_address untuk alamat pengiriman
            $table->string('city', 100); // Kolom city untuk kota pengiriman
            $table->string('postal_code', 20); // Kolom postal_code untuk kode pos pengiriman
            $table->dateTime('shipping_date'); // Kolom shipping_date untuk tanggal pengiriman
            $table->dateTime('estimated_delivery'); // Kolom estimated_delivery untuk perkiraan tanggal pengiriman
            $table->enum('status', ['In progress', 'Shipped', 'Delivered', 'Cancelled']); // Kolom status dengan enum
            $table->timestamps(); // Kolom created_at dan updated_at

            // Menambahkan foreign key constraint
            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->onDelete('cascade');
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
