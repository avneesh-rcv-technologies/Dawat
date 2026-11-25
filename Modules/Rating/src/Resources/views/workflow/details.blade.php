@php
    $customer = $rating->order?->customer ?? $rating->user;
    $customerName = $customer?->name ?? $rating->guest_name ?? 'Customer';
    $customerEmail = $customer?->email ?? $rating->guest_email;
    $item = $rating->ratable?->name ?? 'Rated item';
    $isRestaurantReview = $rating->ratable_type === \Modules\Restaurant\Models\Restaurant::class;
    $status = $owner
        ? ($rating->admin_status === 'pending' ? ($rating->restaurant_status ?: 'pending') : $rating->admin_status)
        : ($rating->admin_status ?: ($rating->is_approved ? 'approved' : 'pending'));
    $statusTone = match ($status) {
        'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'rejected' => 'bg-rose-50 text-rose-700 ring-rose-100',
        default => 'bg-amber-50 text-amber-700 ring-amber-100',
    };
    $decisionUrl = $owner
        ? route('restaurant.ratings.decision', $rating->id)
        : route('ratings.workflow.admin.decision', $rating->id);
@endphp

<article class="overflow-hidden rounded-xl bg-white text-slate-800">
    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-orange-100 bg-gradient-to-r from-orange-50 to-white px-4 py-3">
        <div class="min-w-0">
            <p class="text-[10px] font-extrabold uppercase tracking-[.15em] text-orange-700">Customer feedback</p>
            <h2 class="mt-0.5 truncate text-lg font-bold leading-6 text-slate-900">{{ $isRestaurantReview ? 'Restaurant review' : 'Food item review' }}</h2>
        </div>
        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusTone }}">
            <span class="h-1.5 w-1.5 rounded-full {{ $status === 'approved' ? 'bg-emerald-500' : ($status === 'rejected' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
            {{ ucfirst($status) }}
        </span>
    </header>

    <div class="space-y-4 px-3 py-4 sm:px-4">
        @if(session('success'))
            <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div role="alert" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">{{ $errors->first() }}</div>
        @endif
        <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            @foreach([
                'Customer' => $customerName,
                'Email' => $customerEmail ?: 'Not provided',
                'Rated item' => $item,
                'Order' => $rating->order?->order_number ?: ($rating->order_id ? '#'.$rating->order_id : 'No linked order'),
                'Submitted' => $rating->created_at?->format('M j, Y g:i A') ?: '—',
                'Visibility' => $rating->status ? 'Public / active' : 'Hidden',
            ] as $label => $value)
                <div class="flex min-w-0 items-center justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50/70 px-3 py-2">
                    <dt class="shrink-0 text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                    <dd class="truncate text-right text-xs font-semibold text-slate-800" title="{{ $value }}">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        <section class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3">
            <div>
                <h3 class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Customer rating</h3>
                <div class="mt-1 flex items-center gap-2">
                    <span class="text-lg leading-none tracking-[.12em] text-amber-500" aria-label="{{ $rating->rating }} out of 5 stars">{{ str_repeat('★', max(0, min(5, (int) $rating->rating))) }}{{ str_repeat('☆', max(0, 5 - min(5, (int) $rating->rating))) }}</span>
                    <strong class="text-sm text-slate-900">{{ $rating->rating }}<span class="font-medium text-slate-500"> / 5</span></strong>
                </div>
            </div>
            @if(!$owner)
                <div class="flex flex-wrap items-center gap-2">
                    <form method="POST" action="{{ $decisionUrl }}">
                        @csrf
                        <input type="hidden" name="decision" value="approved">
                        <button class="rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">Approve</button>
                    </form>
                    <details class="relative">
                        <summary class="cursor-pointer list-none rounded-lg border border-rose-200 bg-white px-3.5 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-50">Reject</summary>
                        <form method="POST" action="{{ $decisionUrl }}" class="absolute right-0 top-full z-20 mt-2 w-72 rounded-xl border border-rose-100 bg-white p-3 shadow-xl">
                            @csrf
                            <input type="hidden" name="decision" value="rejected">
                            <label class="text-xs font-semibold text-slate-700">Reason for rejection</label>
                            <textarea name="rejection_reason" required maxlength="2000" rows="3" placeholder="Explain why this review is being rejected" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-100"></textarea>
                            <button class="mt-2 w-full rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white hover:bg-rose-700">Confirm rejection</button>
                        </form>
                    </details>
                </div>
            @elseif($rating->ratable_type === \Modules\FoodManagements\Models\FoodItem::class && $rating->admin_status === 'pending')
                <div class="flex flex-wrap items-center gap-2">
                    <form method="POST" action="{{ $decisionUrl }}">
                        @csrf
                        <input type="hidden" name="decision" value="approved">
                        <button class="rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">Approve</button>
                    </form>
                    <details class="relative">
                        <summary class="cursor-pointer list-none rounded-lg border border-rose-200 bg-white px-3.5 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-50">Reject</summary>
                        <form method="POST" action="{{ $decisionUrl }}" class="absolute right-0 top-full z-20 mt-2 w-72 rounded-xl border border-rose-100 bg-white p-3 shadow-xl">
                            @csrf
                            <input type="hidden" name="decision" value="rejected">
                            <label class="text-xs font-semibold text-slate-700">Reason for rejection</label>
                            <textarea name="rejection_reason" required maxlength="2000" rows="3" placeholder="Explain why this review is being rejected" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-100"></textarea>
                            <button class="mt-2 w-full rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white hover:bg-rose-700">Confirm rejection</button>
                        </form>
                    </details>
                </div>
            @endif
        </section>

        <section>
            <h3 class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Customer message</h3>
            <div class="mt-1.5 max-h-36 overflow-y-auto whitespace-pre-wrap break-words rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-700">{{ $rating->review ?: 'The customer did not leave a written review.' }}</div>
        </section>

        @if($rating->rejection_reason)
            <section class="rounded-xl border border-rose-100 bg-rose-50/70 px-4 py-3">
                <h3 class="text-[10px] font-bold uppercase tracking-wide text-rose-700">Rejection reason</h3>
                <p class="mt-1 whitespace-pre-wrap break-words text-sm leading-5 text-rose-800">{{ $rating->rejection_reason }}</p>
            </section>
        @endif

        @if($rating->restaurant_reply)
            <section class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                <h3 class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Restaurant reply</h3>
                <p class="mt-1 whitespace-pre-wrap break-words text-sm leading-5 text-slate-700">{{ $rating->restaurant_reply }}</p>
            </section>
        @endif

        @if($owner && $status === 'approved')
            <form method="POST" action="{{ route('restaurant.ratings.reply', $rating->id) }}" class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                @csrf
                <label class="text-xs font-bold text-slate-700">Reply publicly to this review</label>
                <textarea name="restaurant_reply" required maxlength="2000" rows="2" placeholder="Write a helpful response" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">{{ $rating->restaurant_reply }}</textarea>
                <button class="mt-2 rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-700">Save reply</button>
            </form>
        @endif

        @unless($owner)
            <form method="POST" action="{{ route('ratings.destroy', $rating->id) }}" onsubmit="return confirm('Delete this review permanently?')" class="flex justify-end border-t border-slate-100 pt-3">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-rose-200 px-3.5 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-50">Delete review</button>
            </form>
        @endunless
    </div>
</article>
