<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropShippingDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('shipping_detail'); // Menghapus tabel jika ada
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('shipping_detail', function (Blueprint $table) {
            $table->id('shipping_id');
            $table->unsignedBigInteger('transaction_id');
            $table->string('shipping_address', 255);
            $table->string('city', 100);
            $table->string('postal_code', 20);
            $table->dateTime('shipping_date');
            $table->dateTime('estimated_delivery');
            $table->enum('status', ['In progress', 'Shipped', 'Delivered', 'Cancelled']);
        });
    }
};
