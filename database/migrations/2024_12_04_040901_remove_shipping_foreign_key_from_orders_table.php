<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveShippingForeignKeyFromOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Menghapus foreign key jika ada
            $table->dropForeign(['shipping_id']);
            // Menghapus kolom shipping_id
            $table->dropColumn('shipping_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Jika migration di-revert, tambahkan kolom shipping_id dan foreign key kembali
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_id')->nullable();
            $table->foreign('shipping_id')->references('id')->on('shippings')->onDelete('SET NULL');
        });
    }
}
