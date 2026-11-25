<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rate your order · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<main class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
    <a href="javascript:history.back()" class="text-sm font-semibold text-slate-500">← Back to order</a>
    <header class="mt-6 rounded-3xl bg-gradient-to-br from-orange-500 to-rose-500 p-7 text-white shadow-lg shadow-orange-200">
        <p class="text-sm font-bold uppercase tracking-widest text-orange-100">Order #{{ $order->order_number }}</p>
        <h1 class="mt-2 text-3xl font-bold">How was your experience?</h1>
        <p class="mt-2 text-orange-50">Your feedback helps {{ $restaurant->name }} serve you better.</p>
    </header>
    @if(session('success'))<div class="mt-5 rounded-2xl bg-emerald-50 p-4 font-medium text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-5 rounded-2xl bg-rose-50 p-4 text-rose-800">{{ $errors->first() }}</div>@endif
    @if($restaurantAlreadyRated)
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-white p-6"><p class="font-semibold text-emerald-700">You’ve already rated this restaurant order.</p></div>
    @else
    <form method="POST" action="{{ route('customer.ratings.submit', $order->id) }}" class="mt-6 space-y-5">@csrf
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <label class="block text-lg font-bold">{{ $restaurant->name }}</label><p class="mt-1 text-sm text-slate-500">Restaurant experience</p>
            <div class="mt-4 flex gap-2" role="radiogroup" aria-label="Restaurant rating">@for($star=1;$star<=5;$star++)<label class="cursor-pointer"><input class="peer sr-only" type="radio" name="restaurant_rating" value="{{ $star }}" required @checked(old('restaurant_rating') == $star)><span class="text-3xl text-slate-300 peer-checked:text-amber-400">★</span><span class="sr-only">{{ $star }} stars</span></label>@endfor</div>
            <textarea name="restaurant_review" maxlength="2000" rows="3" placeholder="Share a few details (optional)" class="mt-4 w-full rounded-xl border-slate-200 focus:border-orange-400 focus:ring-orange-400">{{ old('restaurant_review') }}</textarea>
        </section>
        @foreach($items as $item)
            @if(!in_array((int) $item->id, $ratedItemIds, true))
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <label class="block text-lg font-bold">{{ $item->item_name }}</label><p class="mt-1 text-sm text-slate-500">Food item · optional</p>
                <div class="mt-4 flex gap-2" role="radiogroup" aria-label="{{ $item->item_name }} rating">@for($star=1;$star<=5;$star++)<label class="cursor-pointer"><input class="peer sr-only" type="radio" name="food_ratings[{{ $item->id }}][rating]" value="{{ $star }}" @checked(old("food_ratings.$item->id.rating") == $star)><span class="text-3xl text-slate-300 peer-checked:text-amber-400">★</span><span class="sr-only">{{ $star }} stars</span></label>@endfor</div>
                <textarea name="food_ratings[{{ $item->id }}][review]" maxlength="2000" rows="2" placeholder="Review this dish (optional)" class="mt-4 w-full rounded-xl border-slate-200 focus:border-orange-400 focus:ring-orange-400">{{ old("food_ratings.$item->id.review") }}</textarea>
            </section>
            @endif
        @endforeach
        <button class="w-full rounded-2xl bg-slate-900 px-6 py-4 text-lg font-bold text-white shadow-lg shadow-slate-200 transition hover:bg-slate-700">Submit review</button>
        <p class="text-center text-xs text-slate-500">Your review is checked before it appears publicly.</p>
    </form>
    @endif
</main>
</body>
</html>
