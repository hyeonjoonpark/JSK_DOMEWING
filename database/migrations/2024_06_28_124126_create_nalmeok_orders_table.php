<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nalmeok_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade');
            $table->foreignId('nalmeok_product_id')->constrained('nalmeok_products')->onDelete('cascade');
            $table->foreignId('delivery_company_id')->constrained('delivery_companies')->onDelete('cascade');
            $table->string('order_number');
            $table->string('product_order_number');
            $table->string('tracking_number')->nullable();
            $table->tinyInteger('status', false, true)->comment('1: NEW, 2: CANCELLED, 3: PENDING, 4: COMPLETED');
            $table->integer('price_then', false, true);
            $table->integer('shipping_fee_then', false, true);
            $table->integer('return_shipping_fee_then', false, true);
            $table->integer('bundle_quantity_then', false, true);
            $table->integer('quantity');
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_address');
            $table->string('receiver_remark')->nullable();
            $table->string('sellwing_remark')->nullable();
            $table->timestamp('created_at')->useCurrent()->notNullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nalmeok_orders');
    }
};
