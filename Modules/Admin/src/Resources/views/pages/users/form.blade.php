<form id="userForm" class="form-ajax" method="POST"
    action="@isset($user) {{ route('users.update', $user->id) }} @else {{ route('users.store') }} @endisset">
    @csrf
    @isset($user)
        @method('PUT')
    @endisset
    <input type="hidden" name="guard" value="admin">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

        <!-- Name Field -->
        <div class="col-span-1">
            <label for="name" class="custom-label required">Name</label>
            <input type="text" name="name" id="name" value="{{ $user->name ?? old('name') }}"
                class="input-field" required>
        </div>

        <!-- Email Field -->
        <div class="col-span-1">
            <label for="email" class="custom-label required">Email</label>
            <input type="email" name="email" id="email" value="{{ $user->email ?? old('email') }}"
                class="input-field" required>
        </div>


        
        <!-- Role Field -->
        <div class="col-span-1">
            <label for="role_id" class="custom-label required">Role</label>
            <select name="role_id" id="role_id" class="input-field" required>
                <option value="">Select Role</option>
                @foreach (fn_get_usergroups(['guard' => 'admin']) as $role)
                    <option value="{{ $role->id }}" data-guard="{{ $role->guard }}"
                        @isset($user) @if ($user->role_id == $role->id) selected @endif @endisset
                        @if (old('role_id') == $role->id) selected @endif>
                        <!-- {{ $role->name }} ({{ ucfirst($role->guard) }}) -->
                        {{ $role->name }} 
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Password Field -->
        <div class="col-span-1">
            <label for="password" class="custom-label">Password</label>
            <input type="password" name="password" id="password" class="input-field"
                @empty($user) required @endempty>

            @isset($user)
                <div class="text-helper">
                    <small>Leave blank to keep current</small>
                </div>
            @endisset
        </div>
        @isset($user)
            <div class="col-span-1">
                <p id="user-status-title" class="custom-label">Status</p>
                <input type="hidden" name="status" value="0">
                <label class="relative inline-flex items-center gap-3 mt-2 cursor-pointer">
                    <input type="checkbox" name="status" id="user-status" value="1"
                        class="sr-only peer user-toggle-input" role="switch" aria-labelledby="user-status-title"
                        @checked((bool) old('status', $user->status))>
                    <span class="user-toggle-track" aria-hidden="true"></span>
                    <span class="user-toggle-off text-sm text-gray-700">Inactive</span>
                    <span class="user-toggle-on text-sm text-gray-700">Active</span>
                </label>
                <div class="error-message text-red-500 text-sm mt-1" id="status-error">@error('status') {{ $message }} @enderror</div>
            </div>
            <div class="col-span-1">
                <p id="user-email-verified-title" class="custom-label">Email Verified</p>
                <input type="hidden" name="email_verified" value="0">
                <label class="relative inline-flex items-center gap-3 mt-2 cursor-pointer">
                    <input type="checkbox" name="email_verified" id="user-email-verified" value="1"
                        class="sr-only peer user-toggle-input" role="switch" aria-labelledby="user-email-verified-title"
                        @checked((bool) old('email_verified', $user->email_verified_at !== null))>
                    <span class="user-toggle-track" aria-hidden="true"></span>
                    <span class="user-toggle-off text-sm text-gray-700">Unverified</span>
                    <span class="user-toggle-on text-sm text-gray-700">Verified</span>
                </label>
                <div class="error-message text-red-500 text-sm mt-1" id="email_verified-error">@error('email_verified') {{ $message }} @enderror</div>
            </div>
        @endisset
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('users.index') }}" class="btn-cancel">Cancel</a>

        <button type="submit" name="button" class="btn-primary">
            @isset($user)
                Update User
            @else
                Create User
            @endisset
        </button>
    </div>
</form>

<style>
    #userForm .user-toggle-input { position: absolute !important; width: 1px !important; height: 1px !important; padding: 0 !important; margin: -1px !important; overflow: hidden !important; clip-path: inset(50%); opacity: 0; }
    #userForm .user-toggle-track { position: relative; display: inline-block; flex-shrink: 0; width: 44px; height: 24px; border-radius: 9999px; background: #e5e7eb; transition: background-color .2s; }
    #userForm .user-toggle-track::after { content: ''; position: absolute; top: 4px; left: 4px; width: 16px; height: 16px; border-radius: 50%; background: #fff; transition: transform .2s; }
    #userForm .user-toggle-input:checked ~ .user-toggle-track { background: #16a34a; }
    #userForm .user-toggle-input:checked ~ .user-toggle-track::after { transform: translateX(20px); }
    #userForm .user-toggle-input:focus-visible ~ .user-toggle-track { outline: 2px solid #3b82f6; outline-offset: 2px; }
    #userForm .user-toggle-on { display: none; }
    #userForm .user-toggle-input:checked ~ .user-toggle-on { display: inline; }
    #userForm .user-toggle-input:checked ~ .user-toggle-off { display: none; }
</style>
