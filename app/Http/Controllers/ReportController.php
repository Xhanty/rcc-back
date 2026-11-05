<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Descarga el reporte de cumpleaños del mes actual en formato XLSX.
     */
    public function downloadBirthdays(): StreamedResponse
    {
        $rows = DB::select('SELECT * FROM vw_cumpleanos_mes_actual ORDER BY dia_cumpleanos ASC');

        $headers = [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="cumpleanos_mes_actual.xlsx"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($rows) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Cumpleaños del Mes');

            // Encabezados de columna
            $columns = [
                'A' => ['title' => '#', 'width' => 6],
                'B' => ['title' => 'ID Asistente', 'width' => 14],
                'C' => ['title' => 'Documento', 'width' => 18],
                'D' => ['title' => 'Nombre Completo', 'width' => 32],
                'E' => ['title' => 'Correo', 'width' => 28],
                'F' => ['title' => 'Teléfono', 'width' => 18],
                'G' => ['title' => 'Fecha Nacimiento', 'width' => 18],
                'H' => ['title' => 'Día Cumpleaños', 'width' => 16],
                'I' => ['title' => 'Mes Cumpleaños', 'width' => 16],
                'J' => ['title' => 'Edad que Cumple este Año', 'width' => 26],
            ];

            $headerRow = 1;
            foreach ($columns as $col => $colData) {
                $sheet->setCellValue("{$col}{$headerRow}", $colData['title']);
                $sheet->getColumnDimension($col)->setWidth($colData['width']);
            }

            // Estilo para el encabezado
            $headerRange = "A{$headerRow}:J{$headerRow}";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D97706'], // Amber-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'B45309'],
                    ],
                ],
            ]);
            $sheet->getRowDimension($headerRow)->setRowHeight(28);

            // Filas de datos
            $rowNumber = 2;
            foreach ($rows as $index => $row) {
                $sheet->setCellValue("A{$rowNumber}", $index + 1);
                $sheet->setCellValue("B{$rowNumber}", $row->asistente_id);
                $sheet->setCellValueExplicit("C{$rowNumber}", (string) $row->documento, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("D{$rowNumber}", $row->nombre);
                $sheet->setCellValue("E{$rowNumber}", $row->correo ?? '');
                $sheet->setCellValueExplicit("F{$rowNumber}", (string) ($row->telefono ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("G{$rowNumber}", $row->fecha_nacimiento ?? '');
                $sheet->setCellValue("H{$rowNumber}", $row->dia_cumpleanos ?? '');
                $sheet->setCellValue("I{$rowNumber}", $row->mes_cumpleanos ?? '');
                $sheet->setCellValue("J{$rowNumber}", $row->edad_que_cumple_este_ano ?? '');

                // Alineación de datos
                $sheet->getStyle("A{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Alternar color de fondo
                if ($rowNumber % 2 === 0) {
                    $sheet->getStyle("A{$rowNumber}:J{$rowNumber}")->getFill()->applyFromArray([
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'], // Amber-50
                    ]);
                }

                $sheet->getRowDimension($rowNumber)->setRowHeight(20);
                $rowNumber++;
            }

            // Bordes para toda la tabla de datos
            if ($rowNumber > 2) {
                $lastRow = $rowNumber - 1;
                $sheet->getStyle("A2:J{$lastRow}")->getBorders()->applyFromArray([
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'E5E7EB'],
                    ],
                ]);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Descarga el reporte de seguimiento de pastoreo en formato XLSX.
     */
    public function downloadPastoreo(): StreamedResponse
    {
        $rows = DB::select('SELECT * FROM vw_seguimiento_pastoreo ORDER BY dias_sin_asistir DESC');

        $headers = [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="seguimiento_pastoreo.xlsx"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($rows) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Seguimiento Pastoreo');

            // Encabezados de columna
            $columns = [
                'A' => ['title' => '#', 'width' => 6],
                'B' => ['title' => 'ID Asistente', 'width' => 14],
                'C' => ['title' => 'Documento', 'width' => 18],
                'D' => ['title' => 'Nombre Completo', 'width' => 32],
                'E' => ['title' => 'Correo', 'width' => 28],
                'F' => ['title' => 'Teléfono', 'width' => 18],
                'G' => ['title' => 'Barrio', 'width' => 22],
                'H' => ['title' => 'Fecha Registro', 'width' => 18],
                'I' => ['title' => 'Última Asistencia', 'width' => 18],
                'J' => ['title' => 'Días sin Asistir', 'width' => 18],
                'K' => ['title' => 'Total Asistencias', 'width' => 18],
                'L' => ['title' => 'Estado de Seguimiento', 'width' => 24],
            ];

            $headerRow = 1;
            foreach ($columns as $col => $colData) {
                $sheet->setCellValue("{$col}{$headerRow}", $colData['title']);
                $sheet->getColumnDimension($col)->setWidth($colData['width']);
            }

            // Estilo para el encabezado
            $headerRange = "A{$headerRow}:L{$headerRow}";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0284C7'], // Sky-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => '0369A1'],
                    ],
                ],
            ]);
            $sheet->getRowDimension($headerRow)->setRowHeight(28);

            // Filas de datos
            $rowNumber = 2;
            foreach ($rows as $index => $row) {
                $sheet->setCellValue("A{$rowNumber}", $index + 1);
                $sheet->setCellValue("B{$rowNumber}", $row->asistente_id);
                $sheet->setCellValueExplicit("C{$rowNumber}", (string) $row->documento, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("D{$rowNumber}", $row->nombre);
                $sheet->setCellValue("E{$rowNumber}", $row->correo ?? '');
                $sheet->setCellValueExplicit("F{$rowNumber}", (string) ($row->telefono ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("G{$rowNumber}", $row->barrio ?? '');
                $sheet->setCellValue("H{$rowNumber}", $row->fecha_registro ?? '');
                $sheet->setCellValue("I{$rowNumber}", $row->ultima_asistencia ?? '');
                $sheet->setCellValue("J{$rowNumber}", $row->dias_sin_asistir !== null ? (int)$row->dias_sin_asistir : '');
                $sheet->setCellValue("K{$rowNumber}", $row->total_asistencias !== null ? (int)$row->total_asistencias : 0);
                $sheet->setCellValue("L{$rowNumber}", $row->estado_seguimiento ?? '');

                // Alineaciones
                $sheet->getStyle("A{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("K{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("L{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Color según estado de seguimiento
                $estado = $row->estado_seguimiento ?? '';
                if (str_contains(strtolower($estado), 'crítico') || str_contains(strtolower($estado), 'critico')) {
                    $sheet->getStyle("L{$rowNumber}")->getFont()->getColor()->setRGB('DC2626'); // Red-600
                    $sheet->getStyle("L{$rowNumber}")->getFont()->setBold(true);
                } elseif (str_contains(strtolower($estado), 'alerta')) {
                    $sheet->getStyle("L{$rowNumber}")->getFont()->getColor()->setRGB('D97706'); // Amber-600
                    $sheet->getStyle("L{$rowNumber}")->getFont()->setBold(true);
                } elseif (str_contains(strtolower($estado), 'activo')) {
                    $sheet->getStyle("L{$rowNumber}")->getFont()->getColor()->setRGB('16A34A'); // Green-600
                    $sheet->getStyle("L{$rowNumber}")->getFont()->setBold(true);
                }

                // Alternar color de fondo
                if ($rowNumber % 2 === 0) {
                    $sheet->getStyle("A{$rowNumber}:L{$rowNumber}")->getFill()->applyFromArray([
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0F9FF'], // Sky-50
                    ]);
                }

                $sheet->getRowDimension($rowNumber)->setRowHeight(20);
                $rowNumber++;
            }

            // Bordes para toda la tabla de datos
            if ($rowNumber > 2) {
                $lastRow = $rowNumber - 1;
                $sheet->getStyle("A2:L{$lastRow}")->getBorders()->applyFromArray([
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'E5E7EB'],
                    ],
                ]);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        };

        return response()->stream($callback, 200, $headers);
    }
}
