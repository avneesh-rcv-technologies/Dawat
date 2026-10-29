<div class="onboarding-review-document">
    <h2>{{ $reviewSummary['restaurant']['name'] }}</h2>
    <p class="report-subtitle">Restaurant onboarding review</p>
    <table class="report-fields report-overview">
        <tbody>
            @foreach([
                'Owner' => $reviewSummary['owner']['name'], 'Email' => $reviewSummary['owner']['email'],
                'Restaurant status' => \Illuminate\Support\Str::headline($reviewSummary['restaurant']['status'] ?? 'Not set'),
                'Onboarding status' => $reviewSummary['restaurant']['onboarding_status_label'],
                'Progress' => $reviewSummary['progress']['approved_required_steps'] . ' of ' . $reviewSummary['progress']['required_steps'] . ' required steps approved',
                'Generated at' => $reviewSummary['generated_at'],
            ] as $label => $value)
                <tr><th scope="row">{{ $label }}</th><td>{{ $value ?: 'Not provided' }}</td></tr>
            @endforeach
        </tbody>
    </table>
    @foreach($reviewSummary['sections'] as $section)
        <section class="report-section">
            <h3>Step {{ $section['number'] }} — {{ $section['label'] }} <span class="report-status">{{ $section['status'] }}</span></h3>
            @if($section['can_skip'])<p class="report-note">Optional step — excluded from required approval progress.</p>@endif
            <table class="report-fields">
                <tbody>
                    @foreach([
                        'Submitted' => $section['submitted_at'],
                        'Resubmitted' => $section['resubmitted_at'] ? \Illuminate\Support\Carbon::parse($section['resubmitted_at'])->format('M d, Y h:i A') : null,
                        'Reviewed' => $section['reviewed_at'], 'Reviewed by' => $section['reviewer'],
                        'Rejection reason' => $section['rejection_reason'],
                    ] as $label => $value)
                        @if(filled($value))<tr><th scope="row">{{ $label }}</th><td>{{ $value }}</td></tr>@endif
                    @endforeach
                    @forelse($section['fields'] as $field)
                        <tr><th scope="row">{{ $field['label'] }}</th><td>{{ $field['value'] }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="report-note">No information submitted for this step.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endforeach
</div>
