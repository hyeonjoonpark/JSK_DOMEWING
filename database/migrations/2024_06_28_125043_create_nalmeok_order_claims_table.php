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
        Schema::create('nalmeok_order_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nalmeok_order_id')->constrained('nalmeok_orders')->onDelete('cascade');
            $table->string('claim_number');
            $table->string('product_claim_number');
            $table->tinyInteger('type', false, true)->comment('1: EXCHANGE, 2: REFUND');
            $table->tinyInteger('status', false, true)->comment('1: NEW, 2: CANCELLED, 3: PENDING, 4: COMPLETED');
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
        Schema::dropIfExists('nalmeok_order_claims');
    }
};
