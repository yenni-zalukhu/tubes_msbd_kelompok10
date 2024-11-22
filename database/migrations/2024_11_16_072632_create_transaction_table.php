<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionTable extends Migration
{
    public function up()
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->increments('transaction_id'); // Primary Key
            $table->unsignedInteger('customer_id'); // Foreign Key
            $table->dateTime('transaction_date'); // Tanggal transaksi
            $table->decimal('total_amount', 10, 2); // Total jumlah transaksi
            $table->enum('status', ['Pending', 'Completed', 'Cancelled']); // Status transaksi
            $table->timestamps(); // Created_at dan updated_at

            // Tambahkan foreign key ke tabel customers
            $table->foreign('customer_id')->references('customer_id')->on('customers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction');
    }
}
