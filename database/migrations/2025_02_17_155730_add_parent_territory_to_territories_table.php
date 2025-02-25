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
        Schema::table('territories', function (Blueprint $table) {
            $table->foreignId('parent_territory_id')->nullable()->constrained('territories')->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('territories', function (Blueprint $table) {
            $table->dropForeign('territories_parent_territory_id_foreign');
            $table->dropColumn(['parent_territory_id']);
        });
    }
};
