<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brick_territory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('territory_id')->constrained()->onDelete('cascade');
            $table->foreignId('brick_id')->constrained()->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('unassigned_at')->nullable();
            $table->timestamps();

            // Уникальное сочетание territory_id и brick_id
            $table->unique(['territory_id', 'brick_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('territory_brick');
    }
};
