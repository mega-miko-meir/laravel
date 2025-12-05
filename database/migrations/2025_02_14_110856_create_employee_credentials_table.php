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
        Schema::create('employee_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // связь с сотрудником
            $table->string('user_name');
            $table->string('system'); // например, "CRM" или "ERP"
            $table->string('login');
            $table->string('password'); // Хранить ЗАХЕШИРОВАННЫЙ пароль!
            $table->string('add_password');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_credentials');
    }
};
