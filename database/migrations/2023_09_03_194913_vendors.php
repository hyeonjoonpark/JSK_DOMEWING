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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->enum('is_active', ['ACTIVE', 'INACTIVE'])->default('ACTIVE'); // ENUM 타입의 is_active 컬럼을 추가하고 기본값을 'ACTIVE'로 설정합니다.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};