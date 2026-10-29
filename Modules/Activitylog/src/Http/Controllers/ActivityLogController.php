<?php

namespace Modules\Activitylog\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Modules\Activitylog\Models\Activity;



class ActivityLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $activities = $this->getFilteredActivities($request);
        $stats = $this->getStats();
        return view('activitylog::activity-log.index', compact('activities', 'stats'));
    }


    private function getFilteredActivities(Request $request)
    {
        $user = auth(\Illuminate\Support\Facades\Auth::getDefaultDriver())->id();
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        // Apply filters
        if ($request->filled('severity')) {
            $query->bySeverity($request->severity);
        }
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereJsonContains('properties', $search);
            });
        }

        return $query->paginate(15);
    }

    public function getActivitiesJson(Request $request)
    {

        // Ensure the user is authenticated for the 'agency' guard
        if (!auth(\Illuminate\Support\Facades\Auth::getDefaultDriver())->check()) {
            // Return a JSON error response for AJAX requests
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        $activities = $this->getFilteredActivities($request);

        return response()->json($activities);
    }


    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:activity_log,id'
        ]);

        Activity::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected activities deleted successfully.'
        ]);
    }

    /**
     * Export activities to CSV
     */
    public function exportCsv(Request $request)
    {
        try {
            $request->validate([
                'severity' => 'nullable|string',
                'category' => 'nullable|string',
                'causer_id' => 'nullable|integer',
                'log_name' => 'nullable|string',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'search' => 'nullable|string',
                'quick_filter' => 'nullable|string|in:today,week,month',
            ]);

            $user = auth(\Illuminate\Support\Facades\Auth::getDefaultDriver())->id();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Build query
            $query = Activity::with(['causer', 'subject'])
                ->where('causer_id', $user)
                ->latest();

            // Filters
            if ($request->filled('severity')) {
                $query->bySeverity($request->severity);
            }

            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            if ($request->filled('causer_id')) {
                $query->where('causer_id', $request->causer_id);
            }

            if ($request->filled('log_name')) {
                $query->where('log_name', $request->log_name);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhereJsonContains('properties', $search);
                });
            }

            if ($request->filled('quick_filter')) {
                switch ($request->quick_filter) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year);
                        break;
                }
            }

            $filename = 'agency_activity_log_' . now()->format('Y-m-d_H-i-s');
            if ($request->filled('severity')) {
                $filename .= '_' . $request->severity;
            }
            if ($request->filled('category')) {
                $filename .= '_' . str_replace(' ', '_', strtolower($request->category));
            }
            if ($request->filled('quick_filter')) {
                $filename .= '_' . $request->quick_filter;
            }
            $filename .= '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ];

            return response()->stream(function () use ($query, $user) {
                $output = fopen('php://output', 'w');

                // Add BOM for Excel
                fwrite($output, "\xEF\xBB\xBF");

                $hasWrittenHeader = false;

                $serialNumber = 1;

                $query->chunk(500, function ($activities) use ($output, &$hasWrittenHeader, &$serialNumber) {
                    foreach ($activities as $activity) {
                        $properties = is_string($activity->properties) ? json_decode($activity->properties, true) : ($activity->properties ?? []);

                        $row = [
                            'ID' => $serialNumber++,
                            'Log Name' => $activity->log_name,
                            'Description' => $activity->description,
                            'Subject Type' => $activity->subject_type ?? 'N/A',
                            'Subject ID' => $activity->subject_id ?? 'N/A',
                            'Causer Type' => $activity->causer_type ?? 'N/A',
                            'Causer ID' => $activity->causer_id ?? 'N/A',
                            'Causer Name' => $activity->causer?->name ?? 'System',
                            'Causer Email' => $activity->causer?->email ?? 'N/A',
                            'Category' => $properties['category'] ?? 'General',
                            'Severity' => $properties['severity'] ?? 'info',
                            'Action Type' => $properties['action_type'] ?? $activity->log_name,
                            'IP Address' => $properties['ip_address'] ?? 'N/A',
                            'User Agent' => $properties['user_agent'] ?? 'N/A',
                            'Location' => $properties['location'] ?? 'Unknown',
                            'Method' => $properties['method'] ?? 'N/A',
                            'URL' => $properties['url'] ?? 'N/A',
                            'Status Code' => $properties['status_code'] ?? 'N/A',
                            'Response Time' => $properties['response_time'] ?? 'N/A',
                            'Memory Usage' => $properties['memory_usage'] ?? 'N/A',
                            'Created At' => $activity->created_at->format('Y-m-d H:i:s'),
                            'Updated At' => $activity->updated_at->format('Y-m-d H:i:s'),
                            'Date' => $activity->created_at->format('Y-m-d'),
                            'Time' => $activity->created_at->format('H:i:s'),
                            'Day of Week' => $activity->created_at->format('l'),
                            'Month' => $activity->created_at->format('F'),
                            'Year' => $activity->created_at->format('Y'),
                            'Properties (JSON)' => json_encode($properties),
                        ];

                        if (!$hasWrittenHeader) {
                            fputcsv($output, array_keys($row));
                            $hasWrittenHeader = true;
                        }

                        fputcsv($output, array_values($row));
                    }

                    flush();
                });

                fclose($output);

                Log::info("Agency CSV Export streamed by user: {$user}");
            }, 200, $headers);
        } catch (Exception $e) {
            Log::error('Agency CSV Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to export CSV file.',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function recent(Request $request)
    {
        $user = auth(\Illuminate\Support\Facades\Auth::getDefaultDriver())->id();
        $activities = Activity::with(['causer', 'subject'])
            ->where('causer_id', $user)
            ->latest()
            ->limit(5)
            ->get();
        return response()->json($activities);
    }

    private function getStats(): array
    {
        return [
            'total' => Activity::count(),
            'today' => Activity::today()->count(),
            'week' => Activity::thisWeek()->count(),
            'critical' => Activity::bySeverity('critical')->count(),
            'warning' => Activity::bySeverity('warning')->count(),
            'info' => Activity::bySeverity('info')->count(),
            'by_category' => Activity::selectRaw('JSON_UNQUOTE(JSON_EXTRACT(properties, "$.category")) as category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_user' => Activity::with('causer')
                ->whereNotNull('causer_id')
                ->get()
                ->groupBy('causer.name')
                ->map->count()
                ->sortDesc()
                ->take(5)
                ->toArray()
        ];
    }

    public function staffActivity()
    {
        return view('staff::activity-log.index');
    }
}
