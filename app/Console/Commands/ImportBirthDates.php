<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportBirthDates extends Command
{
    protected $signature = 'employees:import-birth-dates
        {file? : Путь к xlsx (по умолчанию — Копия Спрвочник по сотрудникам.xlsx в Загрузках)}
        {--sheet=Справ : Имя листа}
        {--apply : Записать в БД; без флага — только отчёт (dry-run)}';

    protected $description = 'Импорт дат рождения сотрудников из Excel-справочника по сопоставлению ФИО';

    /** Колонки листа "Справ": C — ФИО, I — дата рождения (см. заголовок строки 1). */
    private const NAME_COL = 'C';
    private const BIRTH_COL = 'I';

    public function handle(): int
    {
        $path = $this->argument('file')
            ?? 'C:\Users\meirzhan.akimbekov\Downloads\Копия Спрвочник по сотрудникам.xlsx';

        if (!is_file($path)) {
            $this->error("Файл не найден: {$path}");
            return self::FAILURE;
        }

        $sheetName = $this->option('sheet');
        $sheet = IOFactory::load($path)->getSheetByName($sheetName);
        if (!$sheet) {
            $this->error("Лист \"{$sheetName}\" не найден в файле");
            return self::FAILURE;
        }

        // ── Читаем файл: [нормализованное_имя => ['name' => исходное, 'date' => Carbon]] ──
        $fileRows = [];
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $name = trim((string) $sheet->getCell(self::NAME_COL . $row)->getValue());
            $raw  = $sheet->getCell(self::BIRTH_COL . $row)->getValue();

            if ($name === '' || !is_numeric($raw)) {
                continue;
            }

            $date = ExcelDate::excelToDateTimeObject((float) $raw);
            // Защита от мусора в ячейке (битые серийные даты и т.п.) — разумный диапазон дат рождения.
            if ($date->format('Y') < 1940 || $date > now()) {
                continue;
            }

            $fileRows[] = ['name' => $name, 'date' => \Carbon\Carbon::instance($date)];
        }

        $this->info('Строк с именем и датой рождения в файле: ' . count($fileRows));

        // ── Сопоставление с сотрудниками системы ──
        $employees = Employee::all(['id', 'full_name', 'birth_date']);

        $byFullName = [];   // нормализованное полное имя => Employee
        $byShortName = [];  // нормализованные первые два слова => [Employee, ...]
        foreach ($employees as $emp) {
            $norm = $this->normalize($emp->full_name);
            if ($norm === '') continue;
            $byFullName[$norm] ??= [];
            $byFullName[$norm][] = $emp;

            $short = $this->normalize($emp->sh_name ?? '');
            if ($short !== '') {
                $byShortName[$short] ??= [];
                $byShortName[$short][] = $emp;
            }
        }

        $toUpdate = [];   // ['employee' => Employee, 'date' => Carbon]
        $conflicts = [];  // уже есть другая дата рождения
        $unmatched = [];
        $ambiguous = [];

        foreach ($fileRows as $row) {
            $norm = $this->normalize($row['name']);

            $candidates = $byFullName[$norm] ?? [];

            if (empty($candidates)) {
                // Короткое имя (Фамилия Имя, без отчества) или наоборот —
                // пробуем по первым двум словам, но только если это даёт
                // РОВНО одного сотрудника, иначе слишком рискованно для
                // персональных данных.
                $shortNorm = $this->normalize(implode(' ', array_slice(explode(' ', $row['name']), 0, 2)));
                $candidates = $byShortName[$shortNorm] ?? [];
            }

            $candidates = collect($candidates)->unique('id')->values();

            if ($candidates->count() === 0) {
                $unmatched[] = $row['name'];
                continue;
            }
            if ($candidates->count() > 1) {
                $ambiguous[] = $row['name'] . ' → ' . $candidates->pluck('full_name')->implode(' / ');
                continue;
            }

            $emp = $candidates->first();

            if ($emp->birth_date) {
                $existing = \Carbon\Carbon::parse($emp->birth_date);
                // Допуск в 1 день — та же причина, что и с hiring_date
                // (timestamp-колонка, встречался артефакт сдвига на часовой пояс).
                if (abs($existing->diffInDays($row['date'], false)) <= 1) {
                    continue; // уже верно, ничего делать не нужно
                }
                $conflicts[] = sprintf(
                    '%s: в системе %s, в файле %s — не перезаписываю',
                    $emp->full_name,
                    $existing->format('d.m.Y'),
                    $row['date']->format('d.m.Y')
                );
                continue;
            }

            $toUpdate[] = ['employee' => $emp, 'date' => $row['date']];
        }

        $this->newLine();
        $this->info('Будет заполнено (сейчас пусто): ' . count($toUpdate));
        foreach ($toUpdate as $u) {
            $this->line("  + {$u['employee']->full_name} → " . $u['date']->format('d.m.Y'));
        }
        $this->warn('Конфликты (уже стоит другая дата, не трогаю): ' . count($conflicts));
        foreach ($conflicts as $c) $this->line("  ! {$c}");
        $this->warn('Неоднозначные (несколько сотрудников под одно ФИО): ' . count($ambiguous));
        foreach ($ambiguous as $a) $this->line("  ? {$a}");
        $this->warn('Не найдено совпадения в системе: ' . count($unmatched));
        foreach (array_slice($unmatched, 0, 30) as $u) $this->line("  - {$u}");
        if (count($unmatched) > 30) $this->line('  ... и ещё ' . (count($unmatched) - 30));

        if (!$this->option('apply')) {
            $this->newLine();
            $this->info('Dry-run: ничего не сохранено. Повторите с --apply, чтобы записать.');
            return self::SUCCESS;
        }

        foreach ($toUpdate as $u) {
            $u['employee']->update(['birth_date' => $u['date']->format('Y-m-d')]);
        }

        $this->newLine();
        $this->info('Сохранено: ' . count($toUpdate));

        return self::SUCCESS;
    }

    private function normalize(string $name): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($name)));
    }
}
