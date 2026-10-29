<div class="dynamic-select-wrapper w-full relative {{ $class }}" data-id="{{ $id ?? 'select_' . uniqid() }}"
    data-multiple="{{ $multiple ? 'true' : 'false' }}" data-placeholder="{{ $placeholder }}"
    data-name="{{ $id }}{{ $multiple ? '[]' : '' }}">

    <!-- Selected tags (for multiple mode) -->
    <div class="selected-tags flex flex-wrap gap-2 mb-2"></div>

    <!-- Input box -->
    <input type="text" class="dynamic-select-input input-field" placeholder="{{ $placeholder }}"
        autocomplete="off">

    <!-- Hidden real select for form submission -->
    <select name="{{ $id }}{{ $multiple ? '[]' : '' }}" @if($id) id="{{ $id }}" @endif class="hidden" @if($multiple)
    multiple @endif>
        @foreach($data as $option)
            <option value="{{ $option['value'] }}" @if($multiple && is_array($selected) && in_array($option['value'], $selected)) selected @endif @if(!$multiple && $selected == $option['value']) selected @endif>
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>

    <!-- Dropdown -->
    <div class="dropdown absolute bg-white border rounded shadow-md w-full max-h-40 overflow-y-auto hidden z-10">
        @foreach($data as $option)
            <div class="dropdown-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="{{ $option['value'] }}">
                {{ $option['label'] }}
            </div>
        @endforeach
    </div>
</div>

@once
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".dynamic-select-wrapper").forEach(wrapper => {
        const input = wrapper.querySelector(".dynamic-select-input");
        const dropdown = wrapper.querySelector(".dropdown");
        const select = wrapper.querySelector("select");
        const selectedTagsContainer = wrapper.querySelector(".selected-tags");
        const multiple = wrapper.dataset.multiple === "true";

        // Initialize selected values (for edit case)
        Array.from(select.options).forEach(opt => {
            if (opt.selected) addTag(opt.value, opt.text);
        });
        refreshDropdown();

        // Show dropdown on focus
        input.addEventListener("focus", () => {
            openDropdown();
            filterOptions();
        });

        // Search filter
        input.addEventListener("input", filterOptions);

        function filterOptions() {
            const search = input.value.toLowerCase();
            dropdown.querySelectorAll(".dropdown-item").forEach(item => {
                const isSelected = select.querySelector(`option[value="${item.dataset.value}"]`).selected;
                item.style.display =
                    item.textContent.toLowerCase().includes(search) && !isSelected ? "" : "none";
            });
        }

        // Click option
        dropdown.addEventListener("click", e => {
            if (!e.target.classList.contains("dropdown-item")) return;
            const value = e.target.dataset.value;
            const label = e.target.textContent;

            if (multiple) {
                if (!select.querySelector(`option[value="${value}"]`).selected) {
                    select.querySelector(`option[value="${value}"]`).selected = true;
                    addTag(value, label);
                }
                input.value = "";
                filterOptions();
            } else {
                // clear previous
                Array.from(select.options).forEach(opt => opt.selected = false);
                select.querySelector(`option[value="${value}"]`).selected = true;
                input.value = label;
                clearTags();
                addTag(value, label, true);
                closeDropdown();
            }
        });

        // Add tag UI
        function addTag(value, label, single = false) {
            if (multiple || single) {
                const tag = document.createElement("span");
                tag.className = "bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-sm flex items-center gap-1";
                tag.textContent = label;

                if (multiple) {
                    const removeBtn = document.createElement("button");
                    removeBtn.type = "button";
                    removeBtn.className = "ml-1 text-red-500";
                    removeBtn.innerHTML = "&times;";
                    removeBtn.addEventListener("click", () => {
                        select.querySelector(`option[value="${value}"]`).selected = false;
                        tag.remove();
                        refreshDropdown();
                    });
                    tag.appendChild(removeBtn);
                }
                selectedTagsContainer.appendChild(tag);
            }
            refreshDropdown();
        }

        function clearTags() {
            selectedTagsContainer.innerHTML = "";
        }

        function refreshDropdown() {
            dropdown.querySelectorAll(".dropdown-item").forEach(item => {
                const selected = select.querySelector(`option[value="${item.dataset.value}"]`).selected;
                item.style.display = selected ? "none" : "";
            });
        }

        function openDropdown() {
            dropdown.classList.remove("hidden");
        }
        function closeDropdown() {
            dropdown.classList.add("hidden");
            input.value = ""; // reset search
        }

        // Close dropdown if clicked outside or ESC
        document.addEventListener("click", e => {
            if (!wrapper.contains(e.target)) closeDropdown();
        });
        input.addEventListener("keydown", e => {
            if (e.key === "Escape") closeDropdown();
        });
    });
});
</script>
@endonce
