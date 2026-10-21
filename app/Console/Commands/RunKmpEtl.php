<?php

namespace App\Console\Commands;

use App\Services\IntegrationStatusStore;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunKmpEtl extends Command
{
    protected $signature = 'etl:kmp {--from= : YYYY-MM-DD — начало диапазона (нужно вместе с --to)}
        {--to= : YYYY-MM-DD — конец диапазона (нужно вместе с --from)}';
    protected $description = 'Забрать БыстрыйОтчёт с nobel.provisor.kz и залить в nobeldb.KMP_data';

    /** Slug в config('integrations') / IntegrationStatusStore — см. страницу /admin/integrations. */
    private const SLUG = 'kmp';

    public function handle(): int
    {
        $scriptPath = base_path('scripts/kmp_etl.py');
        $from       = $this->option('from');
        $to         = $this->option('to');

        if (($from && !$to) || ($to && !$from)) {
            $this->error('--from и --to нужно задавать вместе');
            return self::FAILURE;
        }

        $python = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $cmd = [$python, $scriptPath];
        if ($from && $to) {
            $cmd[] = '--from=' . $from;
            $cmd[] = '--to=' . $to;
        }

        $mainDb = config('database.connections.mysql');
        $env = [
            'KMP_PORTAL_USERNAME' => config('services.kmp_portal.username'),
            'KMP_PORTAL_PASSWORD' => config('services.kmp_portal.password'),
            'KMP_NOBELDB_USER'    => config('services.kmp_portal.nobeldb_write_user'),
            'KMP_NOBELDB_PASS'    => config('services.kmp_portal.nobeldb_write_password'),
            'KMP_SW_REFERENCE_FILE' => config('services.kmp_portal.sw_reference_file'),
            'KMP_MAIN_DB_HOST'    => $mainDb['host'],
            'KMP_MAIN_DB_PORT'    => $mainDb['port'],
            'KMP_MAIN_DB_NAME'    => $mainDb['database'],
            'KMP_MAIN_DB_USER'    => $mainDb['username'],
            'KMP_MAIN_DB_PASS'    => $mainDb['password'],
        ];

        IntegrationStatusStore::write(self::SLUG, [
            'status'      => 'running',
            'started_at'  => now()->toDateTimeString(),
            'finished_at' => null,
            'message'     => null,
        ]);

        $this->info('Запуск ETL КМП: ' . implode(' ', $cmd));

        $process = new Process($cmd, base_path(), $env, null, 3600);
        $process->run(function (string $_, string $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $message = 'ETL КМП завершился с ошибкой (код ' . $process->getExitCode() . ')';
            $this->error($message);
            IntegrationStatusStore::write(self::SLUG, [
                'status'      => 'failed',
                'finished_at' => now()->toDateTimeString(),
                'message'     => $message,
            ]);
            return self::FAILURE;
        }

        $this->info('ETL КМП успешно завершён.');
        IntegrationStatusStore::write(self::SLUG, [
            'status'      => 'success',
            'finished_at' => now()->toDateTimeString(),
            'message'     => 'Данные успешно обновлены.',
        ]);
        return self::SUCCESS;
    }
}
