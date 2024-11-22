<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id'); // Kolom customer_id (primary key dengan auto-increment)
            $table->string('name'); // Kolom name
            $table->string('phone', 20); // Kolom phone dengan panjang 20 karakter
            $table->string('email')->unique(); // Kolom email, set unique
            $table->string('address'); // Kolom address
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
