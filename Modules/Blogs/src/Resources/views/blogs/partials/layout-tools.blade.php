<div class="my-3 flex flex-wrap items-center gap-3">
    <button type="button" data-blog-introduction class="rounded border border-gray-300 px-3 py-2 text-sm">Insert introduction above cover</button>
    <button type="button" data-blog-columns class="rounded border border-gray-300 px-3 py-2 text-sm">Insert image + text section</button>
    <span class="text-xs text-gray-500">Use Heading 2 for section titles. The short description appears above the cover; Inner Image is the cover and Outer Image is the listing thumbnail.</span>
</div>
<script>
(() => {
    document.querySelector('[data-blog-introduction]')?.addEventListener('click', () => {
        const editor = window.tinymce?.get('long-description-editor');
        if (!editor || editor.dom.select('.blog-introduction').length) return;
        editor.setContent('<div class="blog-introduction"><p>Write your opening paragraphs here. This introduction appears above the author and cover image.</p></div>' + editor.getContent());
        editor.save();
    });
    document.querySelector('[data-blog-columns]')?.addEventListener('click', () => {
        const editor = window.tinymce?.get('long-description-editor');
        if (!editor) return;
        editor.insertContent('<div class="blog-columns"><div><p>Insert your image here using the Image toolbar button.</p></div><div><h2>Section heading</h2><p>Add your section text here.</p><h2>Another heading</h2><p>Add more text here.</p></div></div><p></p>');
        editor.save();
    });
})();
</script>
