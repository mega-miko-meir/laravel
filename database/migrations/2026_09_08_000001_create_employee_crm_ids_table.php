<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_crm_ids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('crm_employee_id');
            // true — привязано вручную через /admin/crm-mapping, false — предложено crm:match-employees
            $table->boolean('confirmed')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            // Один и тот же CRM-аккаунт нельзя привязать к двум разным сотрудникам,
            // но у одного сотрудника таких строк может быть сколько угодно (many-to-one).
            $table->unique('crm_employee_id');
        });

        // Переносим существующие 1:1 привязки из employees.crm_employee_id
        if (Schema::hasColumn('employees', 'crm_employee_id')) {
            $now = now();
            DB::table('employees')
                ->whereNotNull('crm_employee_id')
                ->orderBy('id')
                ->select('id', 'crm_employee_id')
                ->chunk(200, function ($rows) use ($now) {
                    $insert = [];
                    foreach ($rows as $row) {
                        $insert[] = [
                            'employee_id'     => $row->id,
                            'crm_employee_id' => $row->crm_employee_id,
                            'confirmed'       => true,
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ];
                    }
                    DB::table('employee_crm_ids')->insert($insert);
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_crm_ids');
    }
};
