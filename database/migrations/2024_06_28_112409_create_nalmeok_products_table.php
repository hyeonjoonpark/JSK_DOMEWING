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
        Schema::create('nalmeok_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ownerclan_category_id')->constrained('ownerclan_category')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->string('code');
            $table->string('name');
            $table->integer('price', false, true);
            $table->tinyInteger('shipping_type', false, true)->comment('1: PREPAID, 2: BUNDLE, 3: FREE');
            $table->integer('shipping_fee', false, true);
            $table->integer('return_shipping_fee', false, true);
            $table->string('image');
            $table->string('keywords');
            $table->longText('detail');
            $table->timestamp('created_at')->useCurrent()->notNullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nalmeok_products');
    }
};
