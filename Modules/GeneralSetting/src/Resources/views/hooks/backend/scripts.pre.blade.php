@php
    $editorType = fn_get_setting('general.editor.type', 'ckeditor');
@endphp

<script>
    window.appEditorType = @json($editorType);
</script>

@if($editorType === 'tinymce')
<script src="https://cdn.tiny.cloud/1/{{ fn_get_setting('general.editor.api_key') }}/tinymce/8/tinymce.min.js"
    referrerpolicy="origin" crossorigin="anonymous"></script>
@elseif($editorType === 'ckeditor')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js" crossorigin="anonymous"></script>
@endif
