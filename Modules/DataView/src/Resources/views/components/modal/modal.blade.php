
<!-- Trigger Button Only -->
@if(empty($color) && !empty($buttonClass))
   
    <button id="modal-trigger-{{ $id }}" class="{{$buttonClass}}" data-ajax-url="{{ $ajaxUrl }}" data-modal-id="{{ $id }}" data-modal-title="{{ $modalTitle ?? 'Modal Title' }}" data-modal-size="{{ $modalSize ?? 'md' }}">
        {!! $buttonText ?? '__' !!}
    </button>
    
@else
  
    <x-button type="submit" 
        id="modal-trigger-{{ $id }}"
        as="{{!empty($buttonClass == 'a') ? 'a' : '' }}"
        class="{{ $color }}" 
        label="{!!$buttonText!!}" 
        icon='' 
        :calls="[
            'data-ajax-url'     => $ajaxUrl,
            'data-modal-id'     => $id,
            'data-modal-title'  => $modalTitle,
            'data-modal-size'   => $modalSize,
            ]"
    />   
    
@endif