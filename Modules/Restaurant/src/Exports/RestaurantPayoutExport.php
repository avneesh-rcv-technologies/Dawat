<?php

namespace Modules\Restaurant\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;

class RestaurantPayoutExport extends DefaultValueBinder implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithCustomCsvSettings, WithCustomValueBinder, WithEvents, WithHeadings, WithMapping, WithStrictNullComparison, WithTitle
{
    public function __construct(private Builder $rows, private string $format) {}

    public function query(): Builder
    {
        return clone $this->rows;
    }

    public function title(): string
    {
        return 'Payouts';
    }

    public function headings(): array
    {
        return ['Order number', 'Calculated at', 'Currency', 'Order amount', 'Commission rate (%)',
            'Original commission', 'Successful refunds', 'Commission reversed', 'Net commission',
            'Original restaurant share', 'Restaurant refund deduction', 'Restaurant payable',
            'Paid to restaurant', 'Recovery due', 'Status', 'Processed at'];
    }

    public function map($row): array
    {
        $date = fn ($value) => $value ? ($this->format === 'csv' ? $value->format('Y-m-d H:i:s') : Date::dateTimeToExcel($value)) : null;

        return [$this->safeText($row->order_number), $date($row->calculated_at), $row->currency,
            (float) $row->order_amount, $this->format === 'csv' ? (float) $row->commission_percentage : (float) $row->commission_percentage / 100,
            (float) $row->platform_commission_amount, (float) $row->refunded_amount, (float) $row->commission_reversed_amount,
            (float) $row->effective_commission, (float) $row->restaurant_amount, (float) $row->restaurant_refund_deduction,
            (float) $row->final_payable_amount, (float) $row->paid_amount, (float) $row->settlementData()['recovery_due_amount'],
            ucfirst(str_replace('_', ' ', $row->status)), $date($row->processed_at)];
    }

    private function safeText(string $value): string
    {
        return preg_match('/^[\s]*[=+@-]/', $value) ? "'".$value : $value;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function getCsvSettings(): array
    {
        return ['use_bom' => true, 'delimiter' => ',', 'line_ending' => "\r\n"];
    }

    public function columnFormats(): array
    {
        return array_fill_keys(['D', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'], '#,##0.00')
            + ['B' => 'yyyy-mm-dd hh:mm', 'E' => '0.00%', 'P' => 'yyyy-mm-dd hh:mm'];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            if ($this->format !== 'xlsx') {
                return;
            }
            $sheet = $event->sheet->getDelegate();
            $last = $sheet->getHighestRow();
            $sheet->freezePane('D2');
            $sheet->setShowGridlines(false);
            $sheet->getStyle('A1:P'.$last)->applyFromArray([
                'font' => ['name' => 'Aptos', 'size' => 11, 'color' => ['rgb' => '171717']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E3E8EB']]],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(34);
            $sheet->getStyle('A1:P1')->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0E2']],
                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'F5C9A5']]],
            ]);
            $sheet->getDefaultRowDimension()->setRowHeight(26);
            $sheet->getSheetView()->setZoomScale(90);
            if ($last > 1) {
                $sheet->getStyle('A2:P'.$last)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                $stripe = new Conditional;
                $stripe->setConditionType(Conditional::CONDITION_EXPRESSION)->addCondition('MOD(ROW(),2)=1');
                $stripe->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFCF8');
                $sheet->getStyle('A2:P'.$last)->setConditionalStyles([$stripe]);
                $sheet->getStyle('D2:N'.$last)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                foreach (['I', 'L'] as $column) {
                    $sheet->getStyle($column.'2:'.$column.$last)->getFont()->setBold(true);
                }
                $table = new Table('A1:P'.$last, 'RestaurantPayouts');
                // Explicit formatting keeps Excel's default green table theme from replacing the UI palette.
                $table->setStyle((new TableStyle)->setTheme(TableStyle::TABLE_STYLE_NONE)->setShowRowStripes(false));
                $sheet->addTable($table);
            }
        }];
    }
}
