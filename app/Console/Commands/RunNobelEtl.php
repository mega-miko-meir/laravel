<?php

namespace App\Console\Commands;

use App\Services\IntegrationStatusStore;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunNobelEtl extends Command
{
    protected $signature = 'etl:nobel {reports?* : Номера отчётов (пусто = все)}
        {--month= : YYYY-MM — переопределяет автоматический расчёт месяца для помесячных отчётов (3, 4, 5)}';
    protected $description = 'Загрузить отчёты из Nobel CRM API в MySQL';

    /** Slug в config('integrations') / IntegrationStatusStore — см. страницу /admin/integrations. */
    private const SLUG = 'nobel_crm';

    public function handle(): int
    {
        $scriptPath = base_path('scripts/nobel_etl.py');
        $reports    = $this->argument('reports');
        $month      = $this->option('month');

        $python = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $cmd = [$python, $scriptPath];
        if ($month) {
            $cmd[] = '--month=' . $month;
        }
        $cmd = array_merge($cmd, $reports);

        IntegrationStatusStore::write(self::SLUG, [
            'status'      => 'running',
            'started_at'  => now()->toDateTimeString(),
            'finished_at' => null,
            'message'     => null,
        ]);

        $this->info('Запуск ETL: ' . implode(' ', $cmd));

        $process = new Process($cmd, base_path(), null, null, 3600);
        $process->run(function (string $_, string $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $message = 'ETL завершился с ошибкой (код ' . $process->getExitCode() . ')';
            $this->error($message);
            IntegrationStatusStore::write(self::SLUG, [
                'status'      => 'failed',
                'finished_at' => now()->toDateTimeString(),
                'message'     => $message,
            ]);
            return self::FAILURE;
        }

        $this->info('ETL успешно завершён.');
        IntegrationStatusStore::write(self::SLUG, [
            'status'      => 'success',
            'finished_at' => now()->toDateTimeString(),
            'message'     => 'Данные успешно обновлены.',
        ]);
        return self::SUCCESS;
    }
}
