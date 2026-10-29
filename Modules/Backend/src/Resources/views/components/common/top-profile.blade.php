@php
    $guard = $guard ?? config('auth.defaults.guard', 'web');
    $user = Auth::guard($guard)->user() ?? Auth::user();
    $userName = $user?->name ?? 'Guest';
    $userEmail = $user?->email ?? '';
    $userRoleName = $user?->role?->name ?? null;
    $avatarUrl = $user ? (fn_get_image('avatar', $user->id)['url'] ?? '') : '';
    $loginRoute = $guard === 'customer' ? route('customer.login') : route('admin.login');
    $profileRoute = route('profile.edit');
    $logoutRoute = $user ? route(Auth::getDefaultDriver() . '.logout') : $loginRoute;
@endphp

<div class="relative flex items-center">

    <!-- DROPDOWN TOGGLE BUTTON -->
    <div class="relative">
        @if ($user)
            <button
                id="user-menu-toggle"
                data-dropdown-toggle="user-dropdown"
                aria-controls="user-dropdown"
                aria-expanded="false"
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-neutral-800 p-0 shadow-sm transition hover:border-white/40 hover:ring-2 hover:ring-white/10 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <span class="sr-only">Open user menu</span>
                <img
                    src="{{ $avatarUrl }}"
                    alt="{{ $userName }} avatar"
                    class="h-9 w-9 rounded-full object-cover">
            </button>
        @else
            <a href="{{ $loginRoute }}" class="flex items-center justify-center rounded-full border-none p-0 h-9 w-9 focus:outline-none bg-neutral-200 text-neutral-700">
                <span class="material-icons-outlined text-base">person</span>
            </a>
        @endif
    </div>

    @if ($user)
        <!-- DROPDOWN MENU -->
        <div
            id="user-dropdown"
            class="absolute right-0 top-12 z-50 hidden w-64 overflow-hidden rounded-xl border border-neutral-200 bg-white text-neutral-800 shadow-xl shadow-black/15">
            <!-- Arrow -->
            <div class="absolute right-3 -top-1 h-3 w-3 rotate-45 border-l border-t border-neutral-200 bg-white"></div>

            <div class="max-h-[min(80vh,480px)] overflow-y-auto p-2">

                <!-- User info -->
                @hook('backend:common.top_profile_user_info.pre')
                <!-- Menu Items -->
                <div class="py-2 border-b border-neutral-200">
                    <div class="mb-1 border-b border-neutral-100 px-3 py-3">
                        <a href="{{ $profileRoute }}" class="block">
                            <div class="truncate text-sm font-semibold text-neutral-900">{{ $userName }}</div>
                            <div class="mt-0.5 truncate text-xs text-neutral-500">{{ $userEmail }}</div>
                            @if($userRoleName)
                                <div class="mt-2 inline-flex rounded-full bg-neutral-100 px-2 py-0.5 text-[11px] font-medium text-neutral-600">{{ $userRoleName }}</div>
                            @endif
                        </a>
                    </div>

                    <a href="{{ $profileRoute }}" class="flex items-center rounded-lg px-3 py-2 text-sm transition hover:bg-neutral-100">
                        <span class="material-icons-outlined mr-2 text-base text-neutral-500">person</span>
                        My profile
                    </a>

                    @hook('backend:common.top_profile_menu_items.post')
                </div>

                <!-- Sign Out -->
                <div class="border-t border-neutral-100 pt-2">
                    <form method="POST" action="{{ $logoutRoute }}"
                        class="form-ajax flex items-center rounded-lg px-3 py-2 text-red-600 transition hover:bg-red-50">
                        @csrf
                        <button type="submit" name="button" class="flex w-full items-center text-left text-sm font-medium text-red-600">
                            <span class="material-icons-outlined mr-2 text-base">logout</span>
                            Sign out
                        </button>
                    </form>
                </div>

            </div>
        </div>
    @endif
</div>
