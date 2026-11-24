@php
    $fileService = app(\Modules\Filemanager\Services\FileService::class);
    $galleryImages = collect($restaurantId ? $fileService->getFiles('restaurant_gallery', $restaurantId) : [])->values();
    $galleryMax = \Modules\Restaurant\Services\RestaurantGalleryService::MAX_IMAGES;
    $galleryCount = $galleryImages->count();
    $galleryRemaining = max(0, $galleryMax - $galleryCount);
    $canUpload = $canUploadGallery ?? true;
    $canDelete = $canDeleteGallery ?? true;
    $deleteTemplate = $galleryDeleteAction ?? route('admin.restaurant.gallery.destroy', ['id' => '__id__']);
    $galleryErrors = array_merge($errors->get('images'), \Illuminate\Support\Arr::flatten($errors->get('images.*')));
@endphp

<style>
    .gl { --gl-brand: #ff6210; --gl-brand-soft: #fff4ed; --gl-brand-ring: rgba(255, 98, 16, .18);
          --gl-ink: #111827; --gl-muted: #6b7280; --gl-line: #e5e7eb; --gl-danger: #dc2626; }
    .gl { background: #fff; border: 1px solid var(--gl-line); border-radius: 14px; overflow: hidden; }
    .gl-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 24px; border-bottom: 1px solid var(--gl-line); }
    .gl-header-main { display: flex; align-items: center; gap: 14px; }
    .gl-icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; background: var(--gl-brand-soft); color: var(--gl-brand); flex-shrink: 0; }
    .gl-title { font-size: 16px; font-weight: 600; color: var(--gl-ink); line-height: 1.3; }
    .gl-desc { font-size: 13px; color: var(--gl-muted); margin-top: 2px; }
    .gl-count { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; min-width: 120px; }
    .gl-count-text { font-size: 12px; font-weight: 600; color: var(--gl-ink); }
    .gl-meter { width: 120px; height: 6px; border-radius: 999px; background: #f3f4f6; overflow: hidden; }
    .gl-meter span { display: block; height: 100%; background: var(--gl-brand); border-radius: 999px; transition: width .2s; }
    .gl-body { padding: 24px; }

    .gl-alert { margin-bottom: 20px; padding: 12px 14px; border-radius: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 13px; }
    .gl-alert p + p { margin-top: 4px; }
    .gl-alert[hidden] { display: none; }

    .gl-drop { display: flex; align-items: center; gap: 18px; padding: 22px 24px; border: 1.5px dashed #d1d5db; border-radius: 12px; background: #fafafa; cursor: pointer; transition: border-color .15s, background-color .15s; }
    .gl-drop:hover, .gl-drop.is-over { border-color: var(--gl-brand); background: var(--gl-brand-soft); }
    .gl-drop:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--gl-brand-ring); border-color: var(--gl-brand); }
    .gl-drop-icon { width: 48px; height: 48px; border-radius: 12px; display: grid; place-items: center; background: #fff; color: var(--gl-brand); border: 1px solid var(--gl-line); flex-shrink: 0; }
    .gl-drop-title { font-size: 14px; font-weight: 600; color: var(--gl-ink); }
    .gl-drop-title u { color: var(--gl-brand); text-decoration: none; }
    .gl-drop-hint { font-size: 12px; color: var(--gl-muted); margin-top: 4px; }
    .gl-full { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-radius: 12px; background: #f9fafb; border: 1px solid var(--gl-line); font-size: 13px; color: #4b5563; }
    .gl-full[hidden], .gl-drop[hidden] { display: none; }

    .gl-section-head { display: flex; align-items: baseline; justify-content: space-between; margin: 28px 0 12px; }
    .gl-section-head h4 { font-size: 13px; font-weight: 600; color: var(--gl-ink); }
    .gl-section-head span { font-size: 12px; color: var(--gl-muted); }
    .gl-section-head[hidden] { display: none; }

    .gl-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    @media (min-width: 640px) { .gl-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (min-width: 1024px) { .gl-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    @media (min-width: 1440px) { .gl-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }

    .gl-tile { position: relative; border-radius: 12px; overflow: hidden; border: 1px solid var(--gl-line); background: #f3f4f6; }
    .gl-tile-img { display: block; aspect-ratio: 4 / 3; width: 100%; }
    .gl-tile-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .25s; }
    .gl-tile:hover .gl-tile-img img { transform: scale(1.03); }
    .gl-tile-meta { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 10px; background: #fff; border-top: 1px solid var(--gl-line); font-size: 12px; color: var(--gl-muted); }
    .gl-tile-meta span:first-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #374151; font-weight: 500; }
    .gl-tile-actions { position: absolute; top: 8px; right: 8px; display: flex; gap: 6px; opacity: 0; transition: opacity .15s; }
    .gl-tile:hover .gl-tile-actions, .gl-tile:focus-within .gl-tile-actions { opacity: 1; }
    @media (hover: none) { .gl-tile-actions { opacity: 1; } }
    .gl-tile-btn { width: 32px; height: 32px; border-radius: 8px; display: grid; place-items: center; background: rgba(255,255,255,.95); color: #374151; border: none; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.15); text-decoration: none; transition: color .15s, background-color .15s; }
    .gl-tile-btn:hover { color: var(--gl-ink); background: #fff; }
    .gl-tile-btn.is-danger:hover { color: #fff; background: var(--gl-danger); }
    .gl-tile-btn:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--gl-brand-ring); }
    .gl-badge { position: absolute; top: 8px; left: 8px; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 999px; background: var(--gl-brand); color: #fff; }
    .gl-tile.is-new { border-color: #fdba8c; }

    .gl-empty { text-align: center; padding: 40px 16px; border: 1px dashed #d1d5db; border-radius: 12px; color: var(--gl-muted); margin-top: 24px; }
    .gl-empty p:first-of-type { font-size: 14px; font-weight: 600; color: #374151; }
    .gl-empty p + p { font-size: 13px; margin-top: 4px; }

    .gl-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 24px; background: #fafafa; border-top: 1px solid var(--gl-line); }
    .gl-footer-note { font-size: 13px; color: var(--gl-muted); }
    .gl-footer-note.is-ready { color: #b45309; font-weight: 500; }
    .gl-btn { height: 44px; padding: 0 20px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-weight: 600; border: 1px solid transparent; background: var(--gl-brand); color: #fff; cursor: pointer; transition: background-color .15s, box-shadow .15s, opacity .15s; }
    .gl-btn:hover:not(:disabled) { background: #e85508; }
    .gl-btn:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--gl-brand-ring); }
    .gl-btn:disabled { opacity: .45; cursor: not-allowed; }
    .gl-btn-ghost { background: transparent; color: var(--gl-muted); }
    .gl-btn-ghost:hover:not(:disabled) { background: #f3f4f6; color: var(--gl-ink); }
    .gl-btn-ghost[hidden] { display: none; }

    @media (max-width: 640px) {
        .gl-header, .gl-body { padding: 16px; }
        .gl-footer { padding: 16px; }
        .gl-count { display: none; }
        .gl-drop { flex-direction: column; text-align: center; }
        .gl-footer .gl-btn:not(.gl-btn-ghost) { width: 100%; }
    }
</style>

<div class="gl" id="restaurant-gallery">
    <div class="gl-header">
        <div class="gl-header-main">
            <span class="gl-icon" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
            <div>
                <h3 class="gl-title">Restaurant Gallery</h3>
                <p class="gl-desc">Photos shown on the restaurant's page in the app</p>
            </div>
        </div>
        <div class="gl-count" aria-hidden="true">
            <span class="gl-count-text"><span data-role="count">{{ $galleryCount }}</span> of {{ $galleryMax }} photos</span>
            <div class="gl-meter"><span data-role="meter" style="width: {{ round($galleryCount / $galleryMax * 100) }}%"></span></div>
        </div>
    </div>

    <form method="POST" action="{{ $galleryStoreAction ?? route('admin.restaurant.gallery.store') }}" enctype="multipart/form-data" id="gallery-form">
        @csrf
        <input type="hidden" name="restaurant_id" value="{{ $restaurantId }}">

        <div class="gl-body">
            <div class="gl-alert" role="alert" id="gallery-errors" @if(empty($galleryErrors)) hidden @endif>
                @foreach ($galleryErrors as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>

            @if ($canUpload)
                <input type="file" name="images[]" id="gallery-input" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" hidden>
                <div class="gl-drop" id="gallery-drop" role="button" tabindex="0" aria-controls="gallery-input"
                    @if($galleryRemaining === 0) hidden @endif>
                    <span class="gl-drop-icon" aria-hidden="true">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </span>
                    <div>
                        <p class="gl-drop-title">Drag photos here or <u>browse</u></p>
                        <p class="gl-drop-hint">JPG, PNG or WebP · up to 2 MB each · <span data-role="remaining">{{ $galleryRemaining }}</span> more allowed</p>
                    </div>
                </div>
                <div class="gl-full" id="gallery-full" @if($galleryRemaining > 0) hidden @endif>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    The gallery is full ({{ $galleryMax }} photos). Delete a photo to add another.
                </div>

                <div class="gl-section-head" id="gallery-pending-head" hidden>
                    <h4>Ready to upload</h4>
                    <span data-role="pending-count"></span>
                </div>
                <div class="gl-grid" id="gallery-pending"></div>
            @endif

            @if ($galleryCount)
                <div class="gl-section-head">
                    <h4>Current photos</h4>
                    <span>{{ $galleryCount }} {{ \Illuminate\Support\Str::plural('photo', $galleryCount) }}</span>
                </div>
                <div class="gl-grid">
                    @foreach ($galleryImages as $img)
                        @php
                            $linkId = $img['file_link_id'] ?? $img['id'];
                            $name = $img['original_name'] ?? $img['file_name'] ?? 'Photo';
                            $preview = $img['medium_url'] ?? $img['url'];
                        @endphp
                        <figure class="gl-tile">
                            <a class="gl-tile-img" href="{{ $img['url'] }}" target="_blank" rel="noopener" aria-label="Open {{ $name }}">
                                <img src="{{ $preview }}" alt="{{ $img['alt_text'] ?? $name }}" loading="lazy"
                                    data-fallback="{{ $img['url'] }}" onerror="this.onerror = null; this.src = this.dataset.fallback;">
                            </a>
                            <div class="gl-tile-actions">
                                <a class="gl-tile-btn" href="{{ $img['url'] }}" target="_blank" rel="noopener" title="View full size" aria-label="View {{ $name }} full size">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                </a>
                                @if ($canDelete)
                                    <button type="button" class="gl-tile-btn is-danger" title="Delete photo" aria-label="Delete {{ $name }}"
                                        onclick="openDeleteModal('{{ str_replace('__id__', $linkId, $deleteTemplate) }}', false)">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            <figcaption class="gl-tile-meta">
                                <span title="{{ $name }}">{{ $name }}</span>
                                <span>{{ $img['formatted_size'] ?? '' }}</span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            @else
                <div class="gl-empty" id="gallery-empty">
                    <p>No photos yet</p>
                    <p>{{ $canUpload ? 'Add photos of your dishes, dining area and storefront.' : 'Photos added to the gallery will appear here.' }}</p>
                </div>
            @endif
        </div>

        @if ($canUpload)
            <div class="gl-footer">
                <p class="gl-footer-note" id="gallery-note">Select photos to upload.</p>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" class="gl-btn gl-btn-ghost" id="gallery-clear" hidden>Clear selection</button>
                    <button type="submit" class="gl-btn" id="gallery-submit" disabled>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span data-role="submit-label">Upload photos</span>
                    </button>
                </div>
            </div>
        @endif
    </form>
</div>

@if ($canUpload)
<script>
(function () {
    'use strict';

    const MAX_BYTES = 2 * 1024 * 1024;
    const ALLOWED = ['jpg', 'jpeg', 'png', 'webp'];
    const remainingSlots = {{ $galleryRemaining }};

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('gallery-form');
        const input = document.getElementById('gallery-input');
        const drop = document.getElementById('gallery-drop');
        const pending = document.getElementById('gallery-pending');
        const pendingHead = document.getElementById('gallery-pending-head');
        const errors = document.getElementById('gallery-errors');
        const note = document.getElementById('gallery-note');
        const submit = document.getElementById('gallery-submit');
        const clearBtn = document.getElementById('gallery-clear');
        const empty = document.getElementById('gallery-empty');
        if (!form || !input) return;

        let selected = [];
        const urls = new Map();

        const formatSize = bytes => bytes >= 1048576 ? `${(bytes / 1048576).toFixed(2)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`;

        function showErrors(messages) {
            errors.innerHTML = '';
            messages.forEach(function (message) {
                const p = document.createElement('p');
                p.textContent = message;
                errors.appendChild(p);
            });
            errors.hidden = messages.length === 0;
        }

        function syncInput() {
            const transfer = new DataTransfer();
            selected.forEach(file => transfer.items.add(file));
            input.files = transfer.files;
        }

        function render() {
            pending.innerHTML = '';
            selected.forEach(function (file, index) {
                if (!urls.has(file)) urls.set(file, URL.createObjectURL(file));
                const tile = document.createElement('figure');
                tile.className = 'gl-tile is-new';
                tile.innerHTML = `
                    <div class="gl-tile-img"><img alt=""></div>
                    <span class="gl-badge">New</span>
                    <div class="gl-tile-actions" style="opacity:1">
                        <button type="button" class="gl-tile-btn is-danger" data-remove="${index}" title="Remove from upload">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <figcaption class="gl-tile-meta"><span></span><span></span></figcaption>`;
                tile.querySelector('img').src = urls.get(file);
                tile.querySelector('img').alt = file.name;
                tile.querySelector('[data-remove]').setAttribute('aria-label', `Remove ${file.name} from upload`);
                tile.querySelector('.gl-tile-meta span:first-child').textContent = file.name;
                tile.querySelector('.gl-tile-meta span:first-child').title = file.name;
                tile.querySelector('.gl-tile-meta span:last-child').textContent = formatSize(file.size);
                pending.appendChild(tile);
            });

            const n = selected.length;
            pendingHead.hidden = n === 0;
            pendingHead.querySelector('[data-role="pending-count"]').textContent = `${n} of ${remainingSlots} slots`;
            if (empty) empty.hidden = n > 0;
            submit.disabled = n === 0;
            clearBtn.hidden = n === 0;
            submit.querySelector('[data-role="submit-label"]').textContent = n ? `Upload ${n} photo${n === 1 ? '' : 's'}` : 'Upload photos';
            note.textContent = n ? `${n} photo${n === 1 ? '' : 's'} ready. They aren't saved until you upload.` : 'Select photos to upload.';
            note.classList.toggle('is-ready', n > 0);
        }

        function addFiles(fileList) {
            const problems = [];
            Array.from(fileList).forEach(function (file) {
                const ext = (file.name.split('.').pop() || '').toLowerCase();
                if (!ALLOWED.includes(ext)) {
                    problems.push(`${file.name}: only JPG, PNG and WebP images are allowed.`);
                } else if (file.size > MAX_BYTES) {
                    problems.push(`${file.name} is ${formatSize(file.size)}. Each photo must be 2 MB or smaller.`);
                } else if (selected.some(f => f.name === file.name && f.size === file.size)) {
                    // Already selected; skip silently.
                } else if (selected.length >= remainingSlots) {
                    problems.push(`${file.name} was skipped: the gallery can hold ${remainingSlots} more photo${remainingSlots === 1 ? '' : 's'}.`);
                } else {
                    selected.push(file);
                }
            });
            showErrors(problems);
            syncInput();
            render();
        }

        drop.addEventListener('click', () => input.click());
        drop.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                input.click();
            }
        });
        input.addEventListener('change', function () {
            // The picker replaces input.files, so merge with what was already chosen.
            const picked = Array.from(input.files);
            syncInput();
            addFiles(picked);
        });

        ['dragenter', 'dragover'].forEach(function (type) {
            drop.addEventListener(type, function (event) {
                event.preventDefault();
                drop.classList.add('is-over');
            });
        });
        ['dragleave', 'drop'].forEach(function (type) {
            drop.addEventListener(type, function (event) {
                event.preventDefault();
                drop.classList.remove('is-over');
            });
        });
        drop.addEventListener('drop', event => addFiles(event.dataTransfer.files));

        pending.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-remove]');
            if (!btn) return;
            const file = selected.splice(Number(btn.dataset.remove), 1)[0];
            if (urls.has(file)) {
                URL.revokeObjectURL(urls.get(file));
                urls.delete(file);
            }
            syncInput();
            render();
        });

        clearBtn.addEventListener('click', function () {
            urls.forEach(url => URL.revokeObjectURL(url));
            urls.clear();
            selected = [];
            showErrors([]);
            syncInput();
            render();
        });

        form.addEventListener('submit', function (event) {
            if (!selected.length) {
                event.preventDefault();
                return;
            }
            submit.disabled = true;
            submit.querySelector('[data-role="submit-label"]').textContent = 'Uploading…';
        });
    });
})();
</script>
@endif
