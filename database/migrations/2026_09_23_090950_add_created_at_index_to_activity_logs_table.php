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
        Schema::table('activity_logs', function (Blueprint $table) {
            // Страница «Активность» всегда сортирует по created_at DESC и теперь
            // умеет фильтровать по диапазону дат — без индекса это full scan
            // по мере роста таблицы (сейчас ~8.8к строк, но растёт непрерывно).
            $table->index('created_at', 'idx_activity_logs_created_at');
            $table->index(['method', 'created_at'], 'idx_activity_logs_method_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_activity_logs_created_at');
            $table->dropIndex('idx_activity_logs_method_created_at');
        });
    }
};
