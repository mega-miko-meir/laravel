<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MatchCrmEmployees extends Command
{
    protected $signature = 'crm:match-employees {--dry-run : Show matches without saving}';
    protected $description = 'Auto-match employees to Nobel CRM by first two name words';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // Use raw query to avoid loading the full qs_calls view into memory
        $rows = DB::connection('nobel')
            ->select('SELECT DISTINCT employee_id, TRIM(employee) as employee FROM qs_calls WHERE employee_id IS NOT NULL AND employee IS NOT NULL AND employee <> ""');

        // Build lookup: trimmed_name => employee_id
        $crmByName = [];
        foreach ($rows as $r) {
            $crmByName[trim($r->employee)] = (int) $r->employee_id;
        }

        $crmEmployees = collect($rows)->mapWithKeys(fn($r) => [(int)$r->employee_id => trim($r->employee)]);

        $this->info("Найдено " . count($rows) . " уникальных сотрудников в CRM");

        $employees = Employee::all();
        $matched = 0;
        $skipped = 0;
        $tableRows = [];

        foreach ($employees as $emp) {
            $shName = $emp->sh_name; // "Фамилия Имя"
            if (!$shName) continue;

            // Match: find CRM name that starts with shName
            $foundCrmId = null;
            $foundCrmName = null;
            foreach ($crmByName as $crmName => $crmId) {
                if (str_starts_with($crmName, $shName)) {
                    $foundCrmId = $crmId;
                    $foundCrmName = $crmName;
                    break;
                }
            }

            if ($foundCrmId !== null) {
                $already = $emp->crm_employee_id === $foundCrmId;
                $tableRows[] = [
                    $emp->id,
                    $emp->full_name,
                    $foundCrmId,
                    $foundCrmName,
                    $already ? 'уже' : 'совпадение',
                ];

                if ($already) {
                    $skipped++;
                } else {
                    $matched++;
                    if (!$dryRun) {
                        $emp->update(['crm_employee_id' => $foundCrmId]);
                    }
                }
            }
        }

        $this->table(['ID', 'Сотрудник', 'CRM ID', 'CRM имя', 'Статус'], $tableRows);

        if ($dryRun) {
            $this->info("Dry-run: будет привязано {$matched} сотрудников (уже привязано: {$skipped})");
        } else {
            $this->info("Привязано: {$matched} | Уже было: {$skipped}");
        }

        return self::SUCCESS;
    }
}
