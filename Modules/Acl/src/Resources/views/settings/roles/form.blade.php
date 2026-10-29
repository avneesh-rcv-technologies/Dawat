@extends('backend::layouts.app')

@section('title', 'Role Management')

@section('breadcrumbs')
    @php
        $isProtectedRole = isset($role) && $role->isProtected();
        $title = isset($role) ? ($isProtectedRole ? 'View Role (' : 'Edit Role (') . $role->name . ')' : 'Create Role';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'System Settings', 'url' => route('settings.index')],
            ['label' => 'Roles & Permission', 'url' => route('settings.roles.index')],
            ['label' => $title, 'url' => null],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    @php
        $selectedPermissionType = $isProtectedRole ? $role->permission_type : old('permission_type', $role->permission_type ?? 'custom');
        $selectedPermissions = $isProtectedRole ? ($role->permissions ?? []) : (old('permissions', $role->permissions ?? []) ?? []);
    @endphp

    <div class="py-6  min-h-screen">
        @if ($isProtectedRole)
            <p role="status" class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">The Admin role is protected. Its details and permissions are read-only.</p>
        @endif

        <form id="role-form" method="POST"
            action="{{ isset($role) ? route('settings.roles.update', $role->id) : route('settings.roles.store') }}"
            class="form-ajax grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            @if (isset($role))
                @method('PUT')
            @endif

            <input type="hidden" name="id" id="role_id" value="{{ $role->id ?? '' }}">
            <fieldset @disabled($isProtectedRole) style="display: contents;">

            {{-- Left Column: Permissions --}}
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                <div class="mb-4 flex justify-between items-center gap-3">
                    <h3 class="text-lg font-semibold text-gray-800">Permissions</h3>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="select-all"
                            class="form-checkbox h-5 w-5 text-primary rounded focus:ring-primary">
                        <span id="select-all-label" class="ml-2 text-sm font-medium text-gray-700">Select All</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label for="permission-search" class="sr-only">Search Permissions</label>
                    <div class="relative mt-1">
                        <input id="permission-search" type="text" placeholder="Search Permissions"
                            class="permission-search input-field pl-11 pr-12 w-full" autocomplete="off">
                        <button type="button" id="clear-permission-search"
                            class="clear-permission-search hidden absolute inset-y-1 right-2 flex items-center rounded px-2 text-sm font-medium text-primary hover:text-primary-dark">
                            Clear
                        </button>
                    </div>
                </div>

                <div id="permissions-tree" class="space-y-4">
                    {{-- @php $permissions = config('acl::acl'); @endphp --}}

                    @php
                        $event = new Modules\Core\Events\RegisterPermissions();
                        event($event);
                        $permissions = $event->getPermissions();
                        use Modules\Core\Helpers\PermissionsVisibility;
                        $selectedGuard = 'admin';
                    @endphp
                    @foreach ($permissions as $guard => $topGroups)
                        @if ($guard === $selectedGuard)
                            @foreach ($topGroups as $topGroup => $subGroups)
                                <div class="permission-group border border-gray-200 rounded-lg p-4 bg-gray-50" data-group-search="{{ strtolower($topGroup) }}">
                                    <label class="flex items-center space-x-2 mb-3 cursor-pointer">
                                        <input type="checkbox" class="group-toggle form-checkbox h-5 w-5 text-primary rounded focus:ring-primary">
                                        <span class="text-base font-bold capitalize text-gray-800">{{ ucfirst($topGroup) }}</span>
                                    </label>
                                    @foreach ($subGroups as $section => $items)
                                        @php
                                            // Only show section if at least one permission is visible for the selected guard
                                            $visible = false;
                                            if (is_array($items)) {
                                                foreach ($items as $permissionKey => $label) {
                                                    if (PermissionsVisibility::visible(['permission' => $permissionKey, 'guard' => $selectedGuard], $selectedGuard)) {
                                                        $visible = true;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $visible = PermissionsVisibility::visible(['permission' => $section, 'guard' => $selectedGuard], $selectedGuard);
                                            }
                                        @endphp
                                        @if ($visible)
                                        <div class="permission-section mb-4" data-search-text="{{ strtolower($topGroup . ' ' . $section) }}" data-section="{{ $topGroup }}-{{ $section }}">
                                            <label class="flex items-center space-x-2 mb-2">
                                                <input type="checkbox"
                                                    class="section-toggle form-checkbox h-5 w-5 text-primary rounded focus:ring-primary"
                                                    data-section="{{ $topGroup }}-{{ $section }}">
                                                <span
                                                    class="text-sm font-semibold text-gray-700 capitalize">{{ ucfirst($section) }}</span>
                                            </label>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 ml-6">
                                                @if (is_array($items))
                                                    @foreach ($items as $permissionKey => $label)
                                                        @if (PermissionsVisibility::visible(['permission' => $permissionKey, 'guard' => $selectedGuard], $selectedGuard))
                                                        <label class="permission-item flex items-center space-x-2"
                                                            data-search-text="{{ strtolower($topGroup . ' ' . $section . ' ' . $label) }}">
                                                            <input type="checkbox" name="permissions[]" value="{{ $permissionKey }}"
                                                                class="permission-checkbox form-checkbox h-4 w-4 text-primary rounded focus:ring-primary"
                                                                @checked($selectedPermissionType === 'all' || in_array($permissionKey, $selectedPermissions, true))>
                                                            <span class="text-sm text-gray-600">{{ $label }}</span>
                                                        </label>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    @if (PermissionsVisibility::visible(['permission' => $section, 'guard' => $selectedGuard], $selectedGuard))
                                                    <label class="permission-item flex items-center space-x-2"
                                                        data-search-text="{{ strtolower($topGroup . ' ' . $section . ' ' . $items) }}">
                                                        <input type="checkbox" name="permissions[]" value="{{ $section }}"
                                                            class="permission-checkbox form-checkbox h-4 w-4 text-primary rounded focus:ring-primary"
                                                            @checked($selectedPermissionType === 'all' || in_array($section, $selectedPermissions, true))>
                                                        <span class="text-sm text-gray-600">{{ $items }}</span>
                                                    </label>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>

                <div id="permission-search-empty" class="hidden mt-4 text-sm text-gray-500">
                    No permissions match your search.
                </div>
            </div>

            {{-- Right Column: Role Details --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="mb-4">
                    <label for="name" class="custom-label">Role Name</label>
                    <input type="text" name="name" id="name" class="input-field" required
                        value="{{ old('name', $role->name ?? '') }}" placeholder="Enter role name">
                </div>

                <div class="mb-4">
                    <label for="description" class="custom-label">Description</label>
                    <input type="text" name="description" id="description" class="input-field"
                        value="{{ old('description', $role->description ?? '') }}" placeholder="Enter role description">
                </div>

                <input type="hidden" name="guard" value="admin">

                <div class="mb-4">
                    <label for="permission_type" class="custom-label">Permission Type</label>
                    <select name="permission_type" id="permission_type" class="input-field">
                        <option value="all" @selected($selectedPermissionType === 'all')>All
                            Permissions</option>
                        <option value="custom" @selected($selectedPermissionType === 'custom')>
                            Custom Permissions</option>
                    </select>
                    <p class="mt-2 text-xs text-gray-500">Checkbox changes use Custom Permissions. Deselecting all saves this role with no permissions. All Permissions grants every permission.</p>
                </div>

                <div class="mt-6">
                    @if ($isProtectedRole)
                        <button type="button" disabled class="btn-primary" style="opacity: .5; cursor: not-allowed;">Read only</button>
                    @else
                        <x-button type="submit" label="Save Role" icon='' name='button' class="btn-primary" />
                    @endif
                </div>
            </div>
            </fieldset>
        </form>
    </div>

    @section('scripts')
        <script>
            function initializeRolePermissions() {
                const form = document.getElementById('role-form');
                if (!form || form.dataset.permissionsBound) return;
                form.dataset.permissionsBound = 'true';

                const selectAll = form.querySelector('#select-all');
                const selectAllLabel = form.querySelector('#select-all-label');
                const searchInput = form.querySelector('#permission-search');
                const clearSearchButton = form.querySelector('#clear-permission-search');
                const emptyState = form.querySelector('#permission-search-empty');
                const permissionType = form.querySelector('#permission_type');
                const sectionToggles = Array.from(form.querySelectorAll('.section-toggle'));
                const groupToggles = Array.from(form.querySelectorAll('.group-toggle'));
                const permissionGroups = Array.from(form.querySelectorAll('.permission-group'));

                function visibleBoxes(container) {
                    if (!container) return [];
                    return Array.from(container.querySelectorAll('.permission-checkbox')).filter(function (box) {
                        return !box.disabled && ['.permission-item', '.permission-section', '.permission-group'].every(function (selector) {
                            const parent = box.closest(selector);
                            return parent && parent.style.display !== 'none';
                        });
                    });
                }

                function updateToggle(toggle, boxes) {
                    if (!toggle) return;
                    const checkedCount = boxes.filter(box => box.checked).length;
                    toggle.checked = boxes.length > 0 && checkedCount === boxes.length;
                    toggle.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
                    toggle.disabled = boxes.length === 0;
                }

                function updateSelectionState() {
                    sectionToggles.forEach(function (toggle) {
                        updateToggle(toggle, visibleBoxes(toggle.closest('.permission-section')));
                    });
                    groupToggles.forEach(function (toggle) {
                        updateToggle(toggle, visibleBoxes(toggle.closest('.permission-group')));
                    });
                    updateToggle(selectAll, visibleBoxes(form));
                }

                function useCustomPermissions() {
                    if (permissionType) permissionType.value = 'custom';
                }

                function setPermissionChecked(box, checked) {
                    // One permission can appear under more than one registered group.
                    form.querySelectorAll('.permission-checkbox').forEach(function (duplicate) {
                        if (duplicate.value === box.value && !duplicate.disabled) duplicate.checked = checked;
                    });
                }

                function applyPermissionSearch() {
                    const query = (searchInput?.value || '').trim().toLowerCase();
                    let visibleGroups = 0;
                    permissionGroups.forEach(function (group) {
                        let hasVisibleSection = false;
                        group.querySelectorAll('.permission-section').forEach(function (section) {
                            const sectionText = (section.dataset.searchText || '').toLowerCase();
                            let visibleItems = 0;
                            section.querySelectorAll('.permission-item').forEach(function (item) {
                                const text = (item.dataset.searchText || item.textContent || '').toLowerCase();
                                const matches = !query || text.includes(query) || sectionText.includes(query);
                                item.style.display = matches ? '' : 'none';
                                if (matches) visibleItems++;
                            });
                            section.style.display = visibleItems > 0 ? '' : 'none';
                            if (visibleItems > 0) hasVisibleSection = true;
                        });
                        group.style.display = hasVisibleSection ? '' : 'none';
                        if (hasVisibleSection) visibleGroups++;
                    });
                    emptyState?.classList.toggle('hidden', !query || visibleGroups > 0);
                    clearSearchButton?.classList.toggle('hidden', !query);
                    if (selectAllLabel) selectAllLabel.textContent = query ? 'Select Results' : 'Select All';
                    updateSelectionState();
                }

                selectAll?.addEventListener('change', function () {
                    useCustomPermissions();
                    visibleBoxes(form).forEach(function (box) { setPermissionChecked(box, selectAll.checked); });
                    updateSelectionState();
                });
                sectionToggles.forEach(function (toggle) {
                    toggle.addEventListener('change', function () {
                        useCustomPermissions();
                        visibleBoxes(toggle.closest('.permission-section')).forEach(function (box) {
                            setPermissionChecked(box, toggle.checked);
                        });
                        updateSelectionState();
                    });
                });
                groupToggles.forEach(function (toggle) {
                    toggle.addEventListener('change', function () {
                        useCustomPermissions();
                        visibleBoxes(toggle.closest('.permission-group')).forEach(function (box) {
                            setPermissionChecked(box, toggle.checked);
                        });
                        updateSelectionState();
                    });
                });
                form.querySelectorAll('.permission-checkbox').forEach(function (box) {
                    box.addEventListener('change', function () {
                        useCustomPermissions();
                        setPermissionChecked(box, box.checked);
                        updateSelectionState();
                    });
                });
                permissionType?.addEventListener('change', function () {
                    if (permissionType.value === 'all') {
                        form.querySelectorAll('.permission-checkbox').forEach(function (box) {
                            if (!box.disabled) box.checked = true;
                        });
                    }
                    updateSelectionState();
                });
                searchInput?.addEventListener('input', applyPermissionSearch);
                searchInput?.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        applyPermissionSearch();
                    }
                });
                clearSearchButton?.addEventListener('click', function () {
                    if (searchInput) {
                        searchInput.value = '';
                        applyPermissionSearch();
                        searchInput.focus();
                    }
                });
                applyPermissionSearch();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeRolePermissions);
            } else {
                initializeRolePermissions();
            }
        </script>
    @endsection
@endsection
