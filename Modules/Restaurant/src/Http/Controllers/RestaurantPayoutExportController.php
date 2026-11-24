<?php

namespace Modules\Restaurant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Orders\Services\OrderCommissionAccess;
use Modules\Restaurant\Exports\RestaurantPayoutExport;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Modules\Restaurant\Services\RestaurantPayoutReport;

class RestaurantPayoutExportController extends Controller
{
    public function __invoke(Request $request, int $id, RestaurantPayoutReport $report)
    {
        $actor = app(OrderCommissionAccess::class)->actor($request);
        $restaurant = Restaurant::query()
            ->when(app(RestaurantApprovalAccess::class)->isOwner($actor), fn ($q) => $q->where('admin_id', $actor->id))
            ->findOrFail($id);
        $format = $request->validate(['format' => ['required', Rule::in(['csv', 'xlsx'])]])['format'];
        $query = $report->query($restaurant->id, $actor, $report->filters($request));
        abort_unless($query->exists(), 404, 'No payout records found.');
        $filename = (Str::slug($restaurant->name) ?: 'restaurant-'.$id).'-payouts-'.now()->format('Ymd-His').'.'.$format;

        $response = Excel::download(new RestaurantPayoutExport($query, $format), $filename,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX,
            ['Cache-Control' => 'no-store, private']);
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }
}
