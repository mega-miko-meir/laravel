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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('first_name')->nullable();
            $table->string('last_name');
            $table->timestamp('birth_date')->nullable();
            $table->string('email')->unique();
            $table->timestamp('hiring_date')->nullable();
            $table->timestamp('firing_date')->nullable();
            $table->string('position');
            $table->string('status');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
