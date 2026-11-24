@if(count($actions))
    <div x-data="{ open: false, top: 0, left: 0 }" @keydown.escape.window="open = false" @resize.window="open = false" @scroll.window="open = false">
        <button type="button" x-ref="trigger" aria-label="Contact query actions" aria-haspopup="menu" :aria-expanded="open"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-100"
            @click="const rect = $refs.trigger.getBoundingClientRect(); top = Math.max(8, Math.min(rect.bottom + 4, window.innerHeight - 112)); left = Math.max(8, rect.right - 192); open = !open">
            <span class="material-icons-outlined" aria-hidden="true">more_vert</span>
        </button>
        {{-- Keep the menu outside the table's scrolling container. --}}
        <template x-teleport="body">
            <div x-show="open" style="display: none; position: fixed; z-index: 1000; width: 192px;"
                :style="{ top: top + 'px', left: left + 'px' }" @click.outside="open = false"
                class="rounded-lg border border-gray-200 bg-white p-1 shadow-lg" role="menu" aria-label="Contact query actions">
                @foreach($actions as $action)
                    @if($action['method'] === 'DELETE')
                        <button type="button" role="menuitem" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-red-600 hover:bg-red-50"
                            @click="open = false; openDeleteModal(@js($action['url']))">
                            <span class="material-icons-outlined" aria-hidden="true">delete</span>Delete
                        </button>
                    @else
                        <a role="menuitem" href="{{ $action['url'] }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <span class="material-icons-outlined" aria-hidden="true">visibility</span>View Details
                        </a>
                    @endif
                @endforeach
            </div>
        </template>
    </div>
@endif
