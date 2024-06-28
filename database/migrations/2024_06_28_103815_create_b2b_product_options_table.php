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
        Schema::create('b2b_product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('b2b_products')->onDelete('cascade');
            $table->string('option_name');
            $table->string('option_values');
            $table->timestamp('created_at')->useCurrent()->notNullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_product_options');
    }
};
