<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Links to shop
            $table->string('shopify_order_id')->unique();
            $table->string('clientemail')->nullable();
            $table->string('clientname')->nullable();
            $table->string('orderid')->index();
            $table->string('shippingtypeName')->nullable();
            $table->string('phone')->nullable();
            $table->string('currency')->nullable();
            $table->string('bill_name')->nullable();
            $table->string('bill_street')->nullable();
            $table->string('bill_street2')->nullable();
            $table->string('bill_city')->nullable();
            $table->string('bill_country')->nullable();
            $table->string('bill_state')->nullable();
            $table->string('bill_zipCode')->nullable();
            $table->string('bill_phone')->nullable();
            $table->string('ship_name')->nullable();
            $table->string('ship_street')->nullable();
            $table->string('ship_street2')->nullable();
            $table->string('ship_city')->nullable();
            $table->string('ship_country')->nullable();
            $table->string('ship_state')->nullable();
            $table->string('ship_zipCode')->nullable();
            $table->string('ship_phone')->nullable();
            $table->text('comments')->nullable();
            $table->decimal('totalpaid', 10, 2)->nullable();
            $table->string('fromwebsite')->default('Shopify');
            $table->string('billingtype')->nullable();
            $table->string('transactionid')->nullable();
            $table->string('order_status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->string('fulfillment_status')->default('unfulfilled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
