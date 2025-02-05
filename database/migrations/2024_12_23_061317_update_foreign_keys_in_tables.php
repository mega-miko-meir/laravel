<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Удаляем старые внешние ключи
        Schema::table('employee_territory', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['territory_id']);
        });

        Schema::table('brick_territory', function (Blueprint $table) {
            $table->dropForeign(['territory_id']);
            $table->dropForeign(['brick_id']);
        });

        // Добавляем новые внешние ключи
        Schema::table('employee_territory', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('restrict');
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('restrict');
        });

        Schema::table('brick_territory', function (Blueprint $table) {
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('restrict');
            $table->foreign('brick_id')->references('id')->on('bricks')->onDelete('restrict');
        });
    }

    public function down()
    {
        // Восстанавливаем старые ключи с CASCADE
        Schema::table('employee_territory', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['territory_id']);
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('cascade');
        });

        Schema::table('brick_territory', function (Blueprint $table) {
            $table->dropForeign(['territory_id']);
            $table->dropForeign(['brick_id']);
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('cascade');
            $table->foreign('brick_id')->references('id')->on('bricks')->onDelete('cascade');
        });
    }
};
