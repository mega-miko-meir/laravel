<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunNobelEtl extends Command
{
    protected $signature = 'etl:nobel {reports?* : Номера отчётов (пусто = все)}';
    protected $description = 'Загрузить отчёты из Nobel CRM API в MySQL';

    public function handle(): int
    {
        $scriptPath = base_path('scripts/nobel_etl.py');
        $reports    = $this->argument('reports');

        $python = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $cmd = array_merge([$python, $scriptPath], $reports);

        $this->info('Запуск ETL: ' . implode(' ', $cmd));

        $process = new Process($cmd, base_path(), null, null, 3600);
        $process->run(function (string $_, string $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->error('ETL завершился с ошибкой (код ' . $process->getExitCode() . ')');
            return self::FAILURE;
        }

        $this->info('ETL успешно завершён.');
        return self::SUCCESS;
    }
}
