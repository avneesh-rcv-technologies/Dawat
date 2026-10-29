<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $reviewSummary['restaurant']['name'] }} — Onboarding Review</title>
    @include('restaurant::admin.approvals.partials.review-styles')
    <style>
        @page { margin: 32px 36px 44px; }
        body { font-family: 'DejaVu Sans', sans-serif; margin: 0; }
        .onboarding-review-document { font-size: 10px; }
        .onboarding-review-document h2 { font-size: 19px; }
        .onboarding-review-document h3 { font-size: 12px; }
        .onboarding-review-document .report-fields th { font-size: 9px; }
        .onboarding-review-document .report-fields th, .onboarding-review-document .report-fields td { padding: 7px 8px; }
        .pdf-footer { position: fixed; bottom: -26px; left: 0; right: 0; color: #64748b; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
    <footer class="pdf-footer">Restaurant onboarding review · {{ $reviewSummary['generated_at'] }}</footer>
    @include('restaurant::admin.approvals.partials.review-content')
</body>
</html>
