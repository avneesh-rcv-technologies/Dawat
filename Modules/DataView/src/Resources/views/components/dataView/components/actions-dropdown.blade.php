<div class="relative inline-block text-left">
    <button data-id="dropdownTrigger_{{$id}}"
        class="dropdown-trigger flex items-center justify-center {{$class ?? 'w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200'}} focus:outline-none focus:ring-1 focus:ring-gray-200 transition-all duration-200">
        {{$title ?? ''}} <span class="material-icons-outlined">{{$icon ?? 'more_vert'}}</span>
    </button>

    <!-- Dropdown menu (hidden by default) -->
    <div data-id="dropdownTrigger_{{$id}}"
        class="dropdown-menu absolute right-0 mt-2 w-48 rounded-md shadow-2xl bg-white   transition-all duration-200 transform opacity-0 scale-95 invisible origin-top-right z-50">
        <div class="px-4   divide divide-y-2 divide-[#D5D7DA]" role="menu" aria-orientation="vertical">
            @foreach($actions as $action)
                <div class="flex flex-col gap-1">
                    @if($action['method'] === 'DELETE')
                        <button onclick="openDeleteModal('{{ $action['url'] }}')"
                            class="dropdown-btn"
                           >
                            <span
                                class="material-icons-outlined  text-base">{{$action['icon']}}</span>&nbsp;{{$action['title']}}
                        </button>
                    @else
                        @if(!empty($action['is_popup']))
                            @php
                                $title = isset($action['icon']) ? "<span class='material-icons-outlined text-base'>" . $action['icon'] . "</span>&nbsp;" . $action['title'] : $action['title'];
                            @endphp
                            <x-modal buttonText="{!! $title !!}" modalTitle="{{ $action['title'] }}" id="{{$action['index']}}"
                                ajaxUrl="{{ $action['url'] }}"
                                buttonClass="dropdown-btn"
                                modalSize="3xl" />
                        @else
                            <a href="{{ $action['url'] }}"
                                class="dropdown-btn"
                                title="{{ $action['title'] }}">
                                @if($action['icon'])
                                    <span
                                        class="material-icons-outlined  text-base">{{ $action['icon'] }}</span>&nbsp;{{ $action['title'] }}
                                @else
                                    {{ $action['title'] }}
                                @endif
                            </a>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>