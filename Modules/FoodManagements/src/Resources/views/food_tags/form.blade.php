<form id="foodTagForm"
      class="form-ajax"
      method="POST"
      action="@isset($tag)
          {{ route('admin.foodmanagements.food-tags.update', $tag->id) }}
      @else
          {{ route('admin.foodmanagements.food-tags.store') }}
      @endisset">

    @csrf
    @isset($tag)
        @method('PUT')
    @endisset

    <input type="hidden" name="id" value="{{ $tag->id ?? 0 }}">

    {{-- Tag Name --}}
    <div class="mb-6">
        <label class="custom-label" for="name">
            Tag Name <span class="text-red-500">*</span>
        </label>

        <input type="text"
               name="name"
               id="name"
               value="{{ old('name', $tag->name ?? '') }}"
               class="input-field @error('name') border-red-500 @enderror"
               placeholder="e.g. Spicy, Bestseller, Chef Special">

        @error('name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Slug (readonly preview) --}}
    @isset($tag)
        <div class="mb-6">
            <label class="custom-label">
                Slug
            </label>
            <input type="text"
                   value="{{ $tag->slug }}"
                   class="input-field bg-gray-100 cursor-not-allowed"
                   readonly>
        </div>
    @endisset

    {{-- Actions --}}
    <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('admin.foodmanagements.food-tags.index') }}"
           class="btn-cancel">
            Cancel
        </a>

        <button type="submit" name="button" class="btn-primary">
            @isset($tag) Update Food Tag @else Create Food Tag @endisset
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('foodTagForm');

    if (!form) return;

    form.addEventListener('submit', function () {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Saving...';

            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerText = '{{ isset($tag) ? 'Update Food Tag' : 'Create Food Tag' }}';
            }, 3000);
        }
    });
});
</script>
