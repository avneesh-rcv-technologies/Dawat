<script>
        function generateSlug() {
            // If editing existing restaurant, do not overwrite slug
            @if ($restaurantId)
                return;
            @endif

            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');

            if (!nameInput || !slugInput) return;

            const slug = nameInput.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            slugInput.value = slug;
        }
</script>
