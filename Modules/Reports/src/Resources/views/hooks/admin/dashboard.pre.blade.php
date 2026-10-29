{{-- Reports Dashboard Widget - Shows reports accessible to current user based on guard, roles, and user assignments
--}}
{{-- <div class="mb-6">
    <x-reports::dashboard-reports :limit="25" />
</div>
--}}


@php
// Check if current user has permission to view reports
$currentAdmin = auth('admin')->user();
$canViewReports = false;

if ($currentAdmin) {
// Check if user has reports.index permission
$canViewReports = bouncer('admin')->hasPermission('reports.index');
}
@endphp

@if($canViewReports)
{{-- Reports Dashboard Widget - Shows reports accessible to current user based on guard, roles, and user assignments
--}}
<div class="mb-6">
    <x-reports::dashboard-reports :limit="25" />
</div>
@endif