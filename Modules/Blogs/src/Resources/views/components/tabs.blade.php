@php
    $tabs = [

        [
            'label' => 'Blogs',
            'route' => route('admin.blogs.blogs.index'),
            'icon' => 'article',  // changed icon
            'permission' => 'admin.blogs.blogs.index',
        ],
        [
            'label' => 'Blog Categories',
            'route' => route('admin.blogs.blog_categories.index'),
            'icon' => 'category',
            'permission' => 'admin.blogs.blog_categories.index',
        ],
        [
            'label' => 'Blog Authors',
            'route' => route('admin.blogs.blog_authors.index'),
            'icon' => 'person',
            'permission' => 'admin.blogs.blog_authors.index',
        ],
        [
            'label' => 'Blog CTAs',
            'route' => route('admin.blogs.blog_ctas.index'),
            'icon' => 'campaign',
            'permission' => 'admin.blogs.blog_ctas.index',
        ],
    ];

    $currentRoute = url()->current();
@endphp


<div class="pt-4">
    <div class="border-b border-gray-200">
        <nav class="flex flex-nowrap overflow-x-auto no-scrollbar -mb-px space-x-6">
            @foreach($tabs as $tab)
                @if(bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission($tab['permission']))
                    @php
                        $isActive = $currentRoute === $tab['route'];
                    @endphp

                    <a href="{{ $tab['route'] }}" class="flex items-center gap-2 whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm 
                                                               {{ $isActive
                    ? 'border-[var(--color-primary-dark)] text-[var(--color-primary-dark)]'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <span class="material-icons-outlined text-base">
                            {{ $tab['icon'] }}
                        </span>
                        {{ $tab['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }
</style>