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
        Schema::create('employee_kmp_names', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('kmp_employee_name');
            // true — привязано вручную через /admin/kmp-mapping, false — предложено авто-мэтчем
            $table->boolean('confirmed')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            // Одно и то же имя КМП нельзя привязать к двум разным сотрудникам,
            // но у одного сотрудника таких строк может быть сколько угодно (many-to-one) —
            // это как раз и покрывает случай повторного найма с новой учёткой КМП.
            $table->unique('kmp_employee_name');
        });

        // Переносим существующие 1:1 привязки из employees.kmp_employee_name
        if (Schema::hasColumn('employees', 'kmp_employee_name')) {
            $now = now();
            DB::table('employees')
                ->whereNotNull('kmp_employee_name')
                ->where('kmp_employee_name', '<>', '')
                ->orderBy('id')
                ->select('id', 'kmp_employee_name')
                ->chunk(200, function ($rows) use ($now) {
                    $insert = [];
                    foreach ($rows as $row) {
                        $insert[] = [
                            'employee_id'       => $row->id,
                            'kmp_employee_name' => $row->kmp_employee_name,
                            'confirmed'         => true,
                            'created_at'        => $now,
                            'updated_at'        => $now,
                        ];
                    }
                    DB::table('employee_kmp_names')->insert($insert);
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_kmp_names');
    }
};
