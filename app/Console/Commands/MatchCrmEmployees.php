<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MatchCrmEmployees extends Command
{
    protected $signature = 'crm:match-employees {--dry-run : Show matches without saving} {--force : Re-match already linked employees too}';
    protected $description = 'Auto-match employees to Nobel CRM by first two name words';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force  = $this->option('force');

        $this->info('Загружаю сотрудников из CRM...');

        $rows = DB::connection('nobel')
            ->select('SELECT employee_id, employee FROM qs_calls WHERE employee_id IS NOT NULL AND employee IS NOT NULL AND employee <> "" GROUP BY employee_id, employee ORDER BY employee_id');

        $crmByName = [];
        foreach ($rows as $r) {
            $name = trim($r->employee);
            if ($name && !isset($crmByName[$name])) {
                $crmByName[$name] = (int) $r->employee_id;
            }
        }

        $this->info('Найдено ' . count($crmByName) . ' уникальных сотрудников в CRM');

        $query = $force
            ? Employee::all()
            : Employee::whereNull('crm_employee_id')->get();

        $this->info('Сотрудников системы для обработки: ' . $query->count());

        $updates = [];
        $matched = 0;
        $skipped = 0;

        foreach ($query as $emp) {
            $shName = $emp->sh_name;
            if (!$shName) continue;

            foreach ($crmByName as $crmName => $crmId) {
                if (str_starts_with($crmName, $shName)) {
                    $this->line("  + [{$emp->id}] {$emp->full_name} → [{$crmId}] {$crmName}");
                    $updates[$emp->id] = $crmId;
                    $matched++;
                    break;
                }
            }
        }

        $this->info("Совпадений: {$matched}");

        if ($dryRun) {
            $this->info('Dry-run: изменения не сохранены');
            return self::SUCCESS;
        }

        if (empty($updates)) {
            $this->info('Нечего обновлять');
            return self::SUCCESS;
        }

        // Bulk update in one transaction
        DB::transaction(function () use ($updates, &$skipped) {
            foreach ($updates as $empId => $crmId) {
                $affected = DB::table('employees')->where('id', $empId)->update(['crm_employee_id' => $crmId]);
                if (!$affected) $skipped++;
            }
        });

        $saved = count($updates) - $skipped;
        $this->info("Сохранено: {$saved} | Пропущено: {$skipped}");

        return self::SUCCESS;
    }
}
