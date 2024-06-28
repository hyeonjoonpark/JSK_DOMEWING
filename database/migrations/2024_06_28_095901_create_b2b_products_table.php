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
        Schema::create('b2b_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('ownerclan_category_id')->constrained('ownerclan_category')->onDelete('cascade');
            $table->tinyInteger('is_active')->default(1)->comment('0: false, 1: true');
            $table->string('code');
            $table->string('name');
            $table->integer('price');
            $table->integer('shipping_fee')->default(3000);
            $table->integer('bundle_quantity')->default(0);
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
        Schema::dropIfExists('b2b_products');
    }
};
