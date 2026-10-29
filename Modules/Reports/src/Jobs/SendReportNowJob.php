<?php

namespace Modules\Reports\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Backend\Services\QueueSyncStatusService;
use Modules\EmailNotification\Mail\NotificationEmail;
use Modules\Notifications\Models\NotificationLog;
use Modules\Reports\Models\Report;
use Modules\Reports\Services\ReportExecutionService;
use Modules\Reports\Services\ReportExportService;

class SendReportNowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?QueueSyncStatusService $syncStatusService = null;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(public int $reportId)
    {
    }

    public function handle(
        ReportExecutionService $executionService,
        ReportExportService $exportService,
        QueueSyncStatusService $syncStatusService
    ): void
    {
        $this->syncStatusService = $syncStatusService;

        $actor = $this->resolveActorFromDefaultRole();

        if (!$actor) {
            return;
        }

        $this->emitStatus($actor, 'queued', [
            'report_id' => $this->reportId,
        ]);

        $report = Report::query()->find($this->reportId);

        if (!$report || !$report->is_active) {
            $this->emitStatus($actor, 'failed', [
                'report_id' => $this->reportId,
                'reason' => !$report ? 'Report not found' : 'Report is inactive',
            ]);
            return;
        }

        $recipients = $this->resolveRecipientsFromDefaultRole();
        if (empty($recipients)) {
            $this->emitStatus($actor, 'failed', [
                'report_id' => $this->reportId,
                'reason' => 'No recipients found',
            ]);
            return;
        }

        $this->emitStatus($actor, 'running', [
            'report_id' => $this->reportId,
        ]);

        try {
            $result = $executionService->execute($report, [], $actor);

            if (!($result['success'] ?? false)) {
                throw new \RuntimeException($result['error'] ?? 'Unable to execute report');
            }

            $format = $this->normalizeExportFormat(fn_get_setting('reports.export_format', 'xlsx'));
            $export = $exportService->export(
                report: $report,
                format: $format,
                data: $result['data'] ?? [],
                exportedBy: $actor,
                executionId: $result['execution_id'] ?? null
            );

            $rows = $result['data'] ?? [];
            $firstRow = !empty($rows) ? (array) $rows[0] : [];
            $columns = array_keys($firstRow);
            $previewRows = array_map(static fn($row) => (array) $row, array_slice($rows, 0, 12));

            $primaryMetric = null;
            if (!empty($columns)) {
                $valueColumn = $columns[1] ?? null;
                if ($valueColumn) {
                    $numericValues = array_values(array_filter(array_map(static function ($row) use ($valueColumn) {
                        $value = (array) $row;
                        $raw = $value[$valueColumn] ?? null;
                        return is_numeric($raw) ? (float) $raw : null;
                    }, $rows), static fn($v) => $v !== null));

                    if (!empty($numericValues)) {
                        $primaryMetric = [
                            'label' => $valueColumn,
                            'sum' => round(array_sum($numericValues), 2),
                            'avg' => round(array_sum($numericValues) / max(1, count($numericValues)), 2),
                            'max' => round(max($numericValues), 2),
                            'min' => round(min($numericValues), 2),
                        ];
                    }
                }
            }

            $meta = [
                'generated_at' => now()->toDateTimeString(),
                'total_rows' => count($rows),
                'execution_time_seconds' => $result['meta']['execution_time_seconds'] ?? 0,
                'chart_type' => $report->chart_type,
                'data_source' => $report->data_source,
                'columns' => $columns,
                'preview_rows' => $previewRows,
                'primary_metric' => $primaryMetric,
                'chart_svg' => (bool) fn_get_setting('reports.attach_chart', 1)
                    ? $this->buildChartSvg($result['data'] ?? [], $report->chart_type)
                    : null,
            ];

            $subject = 'Report: ' . $report->name;
            $body = 'Please find your report attached.';

            $content = view('reports::emails.scheduled_report', [
                'subjectLine' => $subject,
                'emailBody' => $body,
                'reportName' => $report->name,
                'meta' => $meta,
            ])->render();

            $attachments = [];
            if (!empty($export->file_path) && Storage::exists($export->file_path)) {
                $attachments[] = [
                    'path' => Storage::path($export->file_path),
                    'name' => $export->filename,
                    'mime' => $this->mimeFromFilename($export->filename),
                ];
            }

            $log = NotificationLog::create([
                'event_code' => 'report_scheduled_send_now',
                'channel_name' => 'email',
                'notifiable_type' => 'raw',
                'notifiable_id' => 0,
                'content' => $content,
                'status' => 'queued',
                'sent_at' => null
            ]);

            $notificationMail = new NotificationEmail($subject, $content, $log->id, $attachments);

            $this->emitStatus($actor, 'completed', [
                'report_id' => $this->reportId,
                'download_link' => route('reports.download_link', ['exportId' => $export->id]),
            ]);

            Mail::mailer(fn_get_setting('general.mail.driver'))
                ->to($recipients)
                ->queue($notificationMail);
        } catch (\Throwable $e) {
            $this->emitStatus($actor, 'failed', [
                'report_id' => $this->reportId,
                'reason' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function emitStatus(array $actor, string $status, array $extra = []): void
    {
        $service = $this->syncStatusService ?? app(QueueSyncStatusService::class);

        $service->emit(
            eventClass: \Modules\Reports\Events\Report::class,
            actor: $actor,
            status: $status,
            payload: array_merge([
                'report_id' => $this->reportId,
            ], $extra),
            context: [
                'report_id' => $this->reportId,
                'job_key' => $this->jobKey(),
                'reason' => $extra['reason'] ?? null,
            ]
        );
    }

    protected function jobKey(): string
    {
        $jobId = $this->job && method_exists($this->job, 'getJobId') ? (string) $this->job->getJobId() : null;

        return implode(':', array_filter([
            static::class,
            (string) $this->reportId,
            $jobId,
        ]));
    }

    protected function resolveActorFromDefaultRole(): ?array
    {
        $roleId = fn_get_setting('reports.default_role', fn_get_setting('settings.reports.default_role', null));

        if (!empty($roleId)) {
            $adminByRole = DB::table('admins')
                ->select('id')
                ->where('role_id', (int) $roleId)
                ->orderBy('id')
                ->first();

            if ($adminByRole) {
                return [
                    'id' => $adminByRole->id,
                    'type' => 'Modules\\Acl\\Models\\Admin',
                ];
            }
        }

        $admin = DB::table('admins')->select('id')->orderBy('id')->first();

        return $admin ? [
            'id' => $admin->id,
            'type' => 'Modules\\Acl\\Models\\Admin',
        ] : null;
    }

    protected function resolveRecipientsFromDefaultRole(): array
    {
        $roleId = fn_get_setting('reports.default_role', fn_get_setting('settings.reports.default_role', null));

        if (!empty($roleId)) {
            $emails = DB::table('admins')
                ->where('role_id', (int) $roleId)
                ->whereNotNull('email')
                ->pluck('email')
                ->toArray();

            $emails = $this->normalizeRecipients($emails);
            if (!empty($emails)) {
                return $emails;
            }
        }

        return $this->normalizeRecipients(
            DB::table('admins')->whereNotNull('email')->limit(1)->pluck('email')->toArray()
        );
    }

    protected function normalizeRecipients(array|string $recipients): array
    {
        if (is_string($recipients)) {
            $recipients = explode(',', $recipients);
        }

        return array_values(array_filter(array_map(function ($email) {
            $email = trim((string) $email);
            return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
        }, $recipients)));
    }

    protected function normalizeExportFormat(string $format): string
    {
        return match (strtolower($format)) {
            'xlsx', 'excel' => 'excel',
            'csv' => 'csv',
            'pdf' => 'pdf',
            'word', 'doc', 'docx' => 'pdf',
            default => 'excel',
        };
    }

    protected function mimeFromFilename(string $filename): ?string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf' => 'application/pdf',
            default => null,
        };
    }

    protected function buildChartSvg(array $rows, string $chartType = 'bar'): ?string
    {
        if (empty($rows)) {
            return null;
        }

        $firstRow = (array) $rows[0];
        $keys = array_keys($firstRow);
        if (count($keys) < 2) {
            return null;
        }

        $labelKey = $keys[0];
        $valueKey = $keys[1];

        $labels = [];
        $values = [];
        foreach (array_slice($rows, 0, 10) as $row) {
            $row = (array) $row;
            $labels[] = (string) ($row[$labelKey] ?? '');
            $values[] = (float) ($row[$valueKey] ?? 0);
        }

        $max = max($values ?: [1]);
        if ($max <= 0) {
            $max = 1;
        }

        $width = 760;
        $height = 320;
        $padding = 35;
        $plotWidth = $width - ($padding * 2);
        $plotHeight = $height - ($padding * 2);
        $bottomY = $height - $padding;
        $rightX = $width - $padding;

        $svg = [];
        $svg[] = "<svg xmlns='http://www.w3.org/2000/svg' width='{$width}' height='{$height}' viewBox='0 0 {$width} {$height}'>";
        $svg[] = "<rect width='100%' height='100%' fill='#FFFFFF'/>";
        $svg[] = "<line x1='{$padding}' y1='{$bottomY}' x2='{$rightX}' y2='{$bottomY}' stroke='#CBD5E1'/>";
        $svg[] = "<line x1='{$padding}' y1='{$padding}' x2='{$padding}' y2='{$bottomY}' stroke='#CBD5E1'/>";

        $count = count($values);

        if (in_array(strtolower($chartType), ['line', 'area'], true)) {
            $stepX = $count > 1 ? $plotWidth / ($count - 1) : $plotWidth;
            $points = [];

            foreach ($values as $i => $v) {
                $x = $padding + ($i * $stepX);
                $y = $bottomY - (($v / $max) * $plotHeight);
                $points[] = round($x, 2) . ',' . round($y, 2);
            }

            if (strtolower($chartType) === 'area' && !empty($points)) {
                $areaPoints = implode(' ', $points) . ' ' . ($padding + $plotWidth) . ',' . $bottomY . ' ' . $padding . ',' . $bottomY;
                $svg[] = "<polygon points='{$areaPoints}' fill='rgba(59,130,246,0.25)' stroke='none'/>";
            }

            $svg[] = "<polyline points='" . implode(' ', $points) . "' fill='none' stroke='#2563EB' stroke-width='2'/>";
        } else {
            $barWidth = $count > 0 ? ($plotWidth / $count) * 0.7 : $plotWidth;
            $gap = $count > 0 ? ($plotWidth / $count) * 0.3 : 0;

            foreach ($values as $i => $v) {
                $x = $padding + ($i * ($barWidth + $gap)) + ($gap / 2);
                $h = ($v / $max) * $plotHeight;
                $y = $bottomY - $h;
                $svg[] = "<rect x='" . round($x, 2) . "' y='" . round($y, 2) . "' width='" . round($barWidth, 2) . "' height='" . round($h, 2) . "' fill='#2563EB' rx='2'/>";
            }
        }

        foreach ($labels as $i => $label) {
            $x = $padding + (($i + 0.5) * ($plotWidth / max(1, count($labels))));
            $safeLabel = e(mb_strimwidth($label, 0, 12, '…'));
            $svg[] = "<text x='" . round($x, 2) . "' y='" . ($height - 10) . "' text-anchor='middle' font-size='10' fill='#64748B'>{$safeLabel}</text>";
        }

        $svg[] = '</svg>';

        return implode('', $svg);
    }
}
