<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BrickExcelService
{
    public function addBricksSheet(Spreadsheet $spreadsheet, iterable $bricks, string $territoryName): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Bricks');
        $sheet->setCellValue('A1', 'Brick Code');
        $sheet->setCellValue('B1', 'Brick Name');
        $sheet->setCellValue('C1', 'Territory Name');
        $sheet->setCellValue('D1', 'Note');

        foreach ($bricks as $index => $brick) {
            $row = $index + 2;
            $sheet->setCellValue("A{$row}", $brick->additional_code);
            $sheet->setCellValue("B{$row}", $brick->description);
            $sheet->setCellValue("C{$row}", $territoryName);
            $sheet->setCellValue("D{$row}", 'Add this brick to territory');
        }

        $this->autoSizeSheet($sheet);
    }

    public function autoSizeSheet(Worksheet $sheet): void
    {
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
    }

    public function saveAndDownload(Spreadsheet $spreadsheet, string $fileName): BinaryFileResponse
    {
        $filePath = storage_path($fileName);
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function save(Spreadsheet $spreadsheet, string $filePath): void
    {
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($filePath);
    }
}
