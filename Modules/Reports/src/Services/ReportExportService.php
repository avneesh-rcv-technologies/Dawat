<?php

namespace Modules\Reports\Services;

use Modules\Reports\Models\Report;
use Modules\Reports\Models\ReportExecution;
use Modules\Reports\Models\ReportExport;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Model;
use Exception;

class ReportExportService
{
    /**
     * Export report to specified format.
     */
    public function export(Report $report, string $format, array $data, $exportedBy = null, ?int $executionId = null): ReportExport
    {
        $actor = $this->normalizeActor($exportedBy);

        if ($actor['id'] === null || $actor['type'] === null) {
            throw new \InvalidArgumentException('Exported by actor is required for report exports.');
        }
        // Create export record
        $export = ReportExport::create([
            'report_id' => $report->id,
            'execution_id' => $executionId,
            'exported_by_id' => $actor['id'],
            'exported_by_type' => $actor['type'],
            'format' => $format,
            'filename' => $this->generateFilename($report, $format),
            'status' => 'processing',
        ]);

        try {
            // Generate export based on format
            $filePath = match ($format) {
                'csv' => $this->exportToCsv($report, $data, $export->filename),
                'excel' => $this->exportToExcel($report, $data, $export->filename),
                'pdf' => $this->exportToPdf($report, $data, $export->filename),
                default => throw new Exception("Unsupported export format: {$format}"),
            };

            // Get file size
            $fileSize = Storage::size($filePath);

            // Update export record
            $export->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'rows_exported' => count($data),
                'expires_at' => now()->addDays(7), // Expire after 7 days
            ]);

            return $export;

        } catch (Exception $e) {
            $export->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Export to CSV.
     */
    protected function exportToCsv(Report $report, array $data, string $filename): string
    {
        $filePath = 'exports/' . $filename;
        
        $csv = fopen('php://temp', 'w+');
        
        // Write headers
        if (!empty($data)) {
            $firstRow = is_object($data[0]) ? (array)$data[0] : $data[0];
            fputcsv($csv, array_keys($firstRow));
            
            // Write data
            foreach ($data as $row) {
                $rowArray = is_object($row) ? (array)$row : $row;
                fputcsv($csv, $rowArray);
            }
        }
        
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        
        Storage::put($filePath, $content);
        
        return $filePath;
    }

    /**
     * Export to Excel.
     */
    protected function exportToExcel(Report $report, array $data, string $filename): string
    {
        $filePath = 'exports/' . $filename;
        
        // Create a simple Excel export
        // Note: This assumes you have maatwebsite/excel installed
        // If not, fallback to CSV format
        
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setTitle($report->name);
            
            // Write headers
            if (!empty($data)) {
                $firstRow = is_object($data[0]) ? (array)$data[0] : $data[0];
                $headers = array_keys($firstRow);
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . '1', $header);
                    $sheet->getStyle($col . '1')->getFont()->setBold(true);
                    $col++;
                }
                
                // Write data
                $row = 2;
                foreach ($data as $dataRow) {
                    $rowArray = is_object($dataRow) ? (array)$dataRow : $dataRow;
                    $col = 'A';
                    foreach ($rowArray as $value) {
                        $sheet->setCellValue($col . $row, $value);
                        $col++;
                    }
                    $row++;
                }
                
                // Auto-size columns
                foreach (range('A', $col) as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'excel');
            $writer->save($tempFile);
            
            $content = file_get_contents($tempFile);
            Storage::put($filePath, $content);
            unlink($tempFile);
            
        } catch (Exception $e) {
            // Fallback to CSV if Excel export fails
            return $this->exportToCsv($report, $data, str_replace('.xlsx', '.csv', $filename));
        }
        
        return $filePath;
    }

    /**
     * Export to PDF.
     */
    protected function exportToPdf(Report $report, array $data, string $filename): string
    {
        $filePath = 'exports/' . $filename;
        
        // Prepare data for PDF view
        $viewData = [
            'report' => $report,
            'data' => $data,
            'generated_at' => now()->format('F d, Y h:i A'),
        ];
        
        try {
            // Create PDF using DomPDF (if installed)
            $pdf = Pdf::loadView('reports::exports.pdf', $viewData);
            $content = $pdf->output();
            Storage::put($filePath, $content);
        } catch (Exception $e) {
            // Fallback: create simple HTML to PDF
            $html = $this->generateSimplePdfHtml($report, $data);
            Storage::put($filePath, $html);
        }
        
        return $filePath;
    }

    /**
     * Generate simple PDF HTML.
     */
    protected function generateSimplePdfHtml(Report $report, array $data): string
    {
        $html = '<html><head><style>
            body { font-family: Arial, sans-serif; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #4CAF50; color: white; }
            h1 { color: #333; }
        </style></head><body>';
        
        $html .= '<h1>' . htmlspecialchars($report->name) . '</h1>';
        $html .= '<p>Generated: ' . now()->format('F d, Y h:i A') . '</p>';
        
        if (!empty($data)) {
            $html .= '<table>';
            $html .= '<thead><tr>';
            $firstRow = is_object($data[0]) ? (array)$data[0] : $data[0];
            foreach (array_keys($firstRow) as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            
            foreach ($data as $row) {
                $rowArray = is_object($row) ? (array)$row : $row;
                $html .= '<tr>';
                foreach ($rowArray as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell ?? '') . '</td>';
                }
                $html .= '</tr>';
            }
            
            $html .= '</tbody></table>';
        }
        
        $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Generate filename for export.
     */
    protected function generateFilename(Report $report, string $format): string
    {
        $slug = \Str::slug($report->name);
        $timestamp = now()->format('Y-m-d-His');
        $extension = match ($format) {
            'excel' => 'xlsx',
            default => $format,
        };
        
        return "{$slug}-{$timestamp}.{$extension}";
    }

    /**
     * Download export file.
     */
    public function download(ReportExport $export)
    {
        if ($export->isExpired()) {
            throw new Exception('Export file has expired');
        }

        if (!Storage::exists($export->file_path)) {
            throw new Exception('Export file not found');
        }

        $export->incrementDownloadCount();

        return Storage::download($export->file_path, $export->filename);
        // create link to download file, this will be used in email notifications and frontend
    }

    /**
     * Clean up expired exports.
     */
    public function cleanupExpiredExports(): int
    {
        $expiredExports = ReportExport::where('expires_at', '<', now())->get();
        
        $count = 0;
        foreach ($expiredExports as $export) {
            if ($export->file_path && Storage::exists($export->file_path)) {
                Storage::delete($export->file_path);
            }
            $export->delete();
            $count++;
        }
        
        return $count;
    }

    /**
     * Normalize actor data for morph columns.
     */
    protected function normalizeActor($actor): array
    {
        if ($actor instanceof Model) {
            return [
                'id' => $actor->getKey(),
                'type' => $actor->getMorphClass(),
            ];
        }

        if (is_array($actor)) {
            return [
                'id' => $actor['id'] ?? null,
                'type' => $actor['type'] ?? null,
            ];
        }

        if (is_object($actor) && method_exists($actor, 'getAuthIdentifier')) {
            return [
                'id' => $actor->getAuthIdentifier(),
                'type' => method_exists($actor, 'getMorphClass') ? $actor->getMorphClass() : get_class($actor),
            ];
        }

        return [
            'id' => $actor ?: null,
            'type' => null,
        ];
    }

    
    public function downloadByExportId($exportId)
    {
        $export = ReportExport::findOrFail($exportId);
        return Storage::url($export->file_path);

    }

}
