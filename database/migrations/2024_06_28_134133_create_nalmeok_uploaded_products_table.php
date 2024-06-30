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
        Schema::create('nalmeok_uploaded_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nalmeok_partner_product_id')->constrained('nalmeok_partner_products')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent()->notNullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nalmeok_uploaded_products');
    }
};
