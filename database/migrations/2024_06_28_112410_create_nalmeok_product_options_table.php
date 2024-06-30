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
        Schema::create('nalmeok_product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nalmeok_product_id')->constrained('nalmeok_products')->onDelete('cascade');
            $table->string('name');
            $table->string('values');
            $table->timestamp('created_at')->useCurrent()->notNullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nalmeok_product_options');
    }
};
