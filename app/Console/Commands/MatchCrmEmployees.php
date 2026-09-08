<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeCrmId;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MatchCrmEmployees extends Command
{
    protected $signature = 'crm:match-employees {--dry-run : Show matches without saving} {--force : Also process employees who already have at least one linked CRM account}';
    protected $description = 'Auto-match employees to Nobel CRM by first two name words (many-to-one: adds a new linked account, does not replace existing ones)';

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

        // CRM-аккаунты, уже привязанные к кому бы то ни было — не переопределяем
        $alreadyLinkedCrmIds = EmployeeCrmId::pluck('crm_employee_id')->flip();

        $query = $force
            ? Employee::all()
            : Employee::whereDoesntHave('crmIds')->get();

        $this->info('Сотрудников системы для обработки: ' . $query->count());

        $updates = [];
        $matched = 0;

        foreach ($query as $emp) {
            $shName = $emp->sh_name;
            if (!$shName) continue;

            foreach ($crmByName as $crmName => $crmId) {
                if ($alreadyLinkedCrmIds->has($crmId)) continue;
                if (str_starts_with($crmName, $shName)) {
                    $this->line("  + [{$emp->id}] {$emp->full_name} → [{$crmId}] {$crmName}");
                    $updates[] = ['employee_id' => $emp->id, 'crm_employee_id' => $crmId];
                    $alreadyLinkedCrmIds->put($crmId, true);
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

        $now = now();
        DB::transaction(function () use ($updates, $now) {
            foreach ($updates as $u) {
                DB::table('employee_crm_ids')->insert([
                    'employee_id'     => $u['employee_id'],
                    'crm_employee_id' => $u['crm_employee_id'],
                    'confirmed'       => false,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }
        });

        $this->info('Сохранено: ' . count($updates));

        return self::SUCCESS;
    }
}
