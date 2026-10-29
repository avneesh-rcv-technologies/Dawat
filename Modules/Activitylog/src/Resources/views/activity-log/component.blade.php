<div class="w-full overflow-auto" x-data="{ currentView: 'cards', expandedCard: null }" @view-changed.window="currentView = $event.detail">
    <div class="mx-auto p-6 space-y-6">
        <!-- View Controls -->
        @include('activitylog::components.activity.activity-controls')

        <!-- Activity Log -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            @include('activitylog::components.activity.activity-log-header', [
            'total' => $activities->total(),
            'currentPage' => $activities->currentPage(),
            'lastPage' => $activities->lastPage(),
            ])

            <!-- Cards View -->
            <div x-show="currentView === 'cards'" class="divide-y divide-gray-200" id="activity-cards-container">
                @forelse($activities as $activity)
                    @include('activitylog::components.activity.activity-card', ['activity' => $activity])
                @empty
                    @include('activitylog::components.activity.empty-state')
                @endforelse
            </div>
            <!-- Pagination -->
            @if($activities->hasPages())
                @include('activitylog::components.activity.activity-pagination', ['activities' => $activities])
            @endif
        </div>
    </div>
</div>
@push('scripts')
<script>
    window.activityLogSearchRoute = "{{ route('activity-log.agency-search') }}";
</script>
@endpush