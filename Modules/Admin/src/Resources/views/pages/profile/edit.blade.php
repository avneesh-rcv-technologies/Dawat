@extends('backend::layouts.app')
@section('title', 'My Profile')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Profile update', 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Profile'" />
@endsection

@section('content')
    <div class="profile-page">
      @php
          $certificateTab = request('tab') === 'dietary_certificates' && $certificateRestaurant;
      @endphp
      @if($certificateRestaurant)
        <nav class="flex gap-6 border-b border-gray-200 mt-6" aria-label="Profile tabs">
          <a href="{{ route('profile.edit') }}" class="py-4 {{ !$certificateTab ? 'text-orange-600 border-b-2 border-orange-500' : 'text-gray-600' }}" @if(!$certificateTab) aria-current="page" @endif>Profile Details</a>
          <a href="{{ route('profile.edit', ['tab' => 'dietary_certificates', 'restaurant_id' => $certificateRestaurant->id]) }}" class="py-4 {{ $certificateTab ? 'text-orange-600 border-b-2 border-orange-500' : 'text-gray-600' }}" @if($certificateTab) aria-current="page" @endif>Dietary Certificates</a>
        </nav>
      @endif
      @if($certificateTab)
        <section class="profile-card bg-white p-6 mt-6">
          <h2 class="text-xl font-semibold">Dietary Certificates</h2>
          <p class="mt-2 text-sm text-gray-500">Upload certificates for the dietary labels you want to use. Each required certificate must be approved by admin before its label can be selected for food items.</p>
          @if($certificateRestaurants->count() > 1)
            <form method="GET" action="{{ route('profile.edit') }}" class="mt-5">
              <input type="hidden" name="tab" value="dietary_certificates">
              <label class="custom-label" for="certificate-restaurant">Restaurant</label>
              <select class="input-field" id="certificate-restaurant" name="restaurant_id" onchange="this.form.submit()">
                @foreach($certificateRestaurants as $ownedRestaurant)
                  <option value="{{ $ownedRestaurant->id }}" @selected($certificateRestaurant->id === $ownedRestaurant->id)>{{ $ownedRestaurant->name }}</option>
                @endforeach
              </select>
            </form>
          @else
            <p class="mt-4 font-medium">{{ $certificateRestaurant->name }}</p>
          @endif
          @include('restaurant::onboarding.dietary-certificates', [
            'correctionRestaurant' => $certificateRestaurant, 'correctionData' => $certificateData, 'profileMode' => true,
          ])
        </section>
      @endif
      <div class="profile-card bg-white rounded-xl shadow-sm overflow-hidden mt-6" @if($certificateTab) hidden @endif>
        
        <form action="{{ route('profile.update') }}" method="POST" 
                class="form-ajax" id="account-form" enctype="multipart/form-data" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="profile-grid grid grid-cols-1 md:grid-cols-3 gap-8 p-8">
                <!-- Profile Image Section -->
                <div class="profile-photo-section md:col-span-1">
                    <div class="profile-photo-panel space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Profile photo</h3>
                            <p class="mt-1 text-sm text-gray-500">Choose an image that helps your team recognize you.</p>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="relative mb-4">
                                <div class="profile-avatar h-32 w-32 rounded-full overflow-hidden border-4 border-white shadow-lg bg-gray-200">
                                    @php
                                    $image = fn_get_image('admin_avatar', $user->id)['url'] ?? '';
                                    @endphp
                                    @if($image)
                                        <img src="{{ $image }}" 
                                                alt="Profile Image" 
                                                class="h-full w-full object-cover" id="profile-image-preview">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center bg-gray-100 text-gray-400" id="profile-image-preview">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <label for="image-upload" aria-label="Choose a profile photo" title="Choose a profile photo" class="profile-avatar-upload absolute bottom-0 right-0 rounded-full p-2 cursor-pointer shadow-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <input type="file" id="image-upload" name="image" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" class="hidden">
                                </label>
                            </div>
                            
                            <p class="text-sm text-gray-500 text-center mt-2">JPG, PNG, or GIF · Up to 2 MB</p>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="image"></p>
                        </div>
                    </div>
                </div>

                <!-- Form Fields Section -->
                <div class="profile-form-section md:col-span-2">
                    <div class="space-y-6">
                        <div>
                            <div class="profile-section-heading">
                                <h3 class="text-lg font-semibold text-gray-900">Personal information</h3>
                                <p>Update the name associated with your account.</p>
                            </div>
                            
                            @hook('backend::profile.update_fields.pre')
                            <!-- Name -->
                            <div class="profile-field mb-5">
                                <label for="name" class="custom-label">Name </label>
                                <input type="text" id="name" name="name" 
                                        value="{{ old('name', $user->name) }}"
                                        class="input-field" 
                                        placeholder="Your full name" required>
                                <p class="mt-1 text-sm text-red-600 error-message" data-field="name"></p>
                            </div>

                            @hook('backend::profile.update_fields.post')

                            <div class="profile-section-heading profile-password-heading">
                                <h3 class="text-lg font-semibold text-gray-900">Change password</h3>
                                <p>Enter your old password to set a new password. Leave all password fields blank to keep your current password.</p>
                            </div>

                            <div class="profile-field mb-5 relative">
                                <label for="current_password" class="custom-label">Old password</label>
                                <input type="password" name="current_password" id="current_password"
                                        class="input-field" autocomplete="off" readonly
                                        placeholder="Enter old password">
                                <p class="mt-1 text-sm text-red-600 error-message" data-field="current_password"></p>
                            </div>
                            
                            <!-- Password -->
                            <div class="profile-field mb-5 relative">
                                <label for="password" class="custom-label">New password</label>
                                <input type="password" name="password" id="password" 
                                        class="input-field" autocomplete="new-password" readonly
                                        placeholder="Enter new password">
                                
                                <p class="mt-1 text-sm text-red-600 error-message" data-field="password"></p>
                            </div>

                            <!-- Confirm Password -->
                            <div class="profile-field mb-5 relative">
                                <label for="password_confirmation" class="custom-label">Confirm new password</label>
                                <input type="password" name="password_confirmation" 
                                        id="password_confirmation"
                                        class="input-field" autocomplete="new-password" readonly
                                        placeholder="Confirm new password">
                                
                                <p class="mt-1 text-sm text-red-600 error-message" 
                                    data-field="password_confirmation"></p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="profile-submit pt-4 border-t border-gray-200">
                            <button type="submit" id="save-button" name="button" 
                                    class="btn-primary">
                                Update Account Details
                            </button>
                            <p id="success-message" class="mt-3 text-green-600 font-medium"></p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
      </div>
    </div>
    

    <script>
        (() => {
            const form = document.getElementById('account-form');
            const passwordFields = ['current_password', 'password', 'password_confirmation']
                .map((name) => form.elements.namedItem(name));

            // Keep saved credentials out of the form until the user activates a field.
            const clearPasswords = () => {
                passwordFields.forEach((field) => {
                    field.value = '';
                    field.readOnly = true;
                });
            };
            passwordFields.forEach((field) => {
                const enableEditing = () => { field.readOnly = false; };
                field.addEventListener('focus', enableEditing);
                field.addEventListener('pointerdown', enableEditing);
            });
            clearPasswords();
            window.addEventListener('pageshow', clearPasswords);
            window.addEventListener('pagehide', clearPasswords);
            form.addEventListener('reset', clearPasswords);
        })();
       
        // Match the server's supported formats before showing a local preview.
        const savedProfilePreview = document.getElementById('profile-image-preview').cloneNode(true);
        document.getElementById('image-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const error = document.querySelector('#account-form [data-field="image"]');
            error.textContent = '';
            const resetPreview = () => document.getElementById('profile-image-preview')
                .replaceWith(savedProfilePreview.cloneNode(true));
            if (!file) {
                resetPreview();
                return;
            }
            const supportedExtension = /\.(jpe?g|png|gif)$/i.test(file.name);
            const supportedMime = !file.type || ['image/jpeg', 'image/png', 'image/gif'].includes(file.type);
            if (!supportedExtension || !supportedMime || file.size > 2 * 1024 * 1024) {
                error.textContent = !supportedExtension || !supportedMime
                    ? 'Choose a JPG, PNG, or GIF image. Other formats are not supported.'
                    : 'The profile photo must not exceed 2 MB.';
                this.value = '';
                resetPreview();
                return;
            }
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profile-image-preview');
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        // Replace the div with an img element
                        const newPreview = document.createElement('img');
                        newPreview.src = e.target.result;
                        newPreview.classList.add('h-full', 'w-full', 'object-cover');
                        newPreview.id = 'profile-image-preview';
                        preview.parentNode.replaceChild(newPreview, preview);
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

    <style>
        .profile-page { max-width: 1240px; margin: 0 auto; padding-bottom: 28px; }
        .profile-card { border: 1px solid #e5eaf0; border-radius: 16px !important; box-shadow: 0 8px 24px rgb(15 23 42 / 5%) !important; }
        .profile-grid { grid-template-columns: minmax(0, 1fr); gap: 36px !important; padding: 30px !important; }
        .profile-photo-section, .profile-form-section { grid-column: 1 / -1; min-width: 0; }
        .profile-photo-panel { position: static; display: flex; width: 100%; max-width: 360px; margin: 0 auto; min-height: 300px; flex-direction: column; justify-content: space-between; padding: 22px; border: 1px solid #e5eaf0; border-radius: 14px; background: linear-gradient(145deg, #f6faf8, #f8fafc 70%); text-align: center; }
        .profile-photo-panel h3, .profile-section-heading h3 { color: #111111; }
        .profile-avatar { border-color: #fff; background: #e8eef2; box-shadow: 0 8px 20px rgb(15 23 42 / 12%); }
        .profile-avatar-upload { display: inline-flex; align-items: center; justify-content: center; border: 3px solid #fff; background: #126746; color: #fff; }
        .profile-avatar-upload:hover { background: #0d5439; }
        .profile-avatar-upload input { cursor: pointer; }
        .profile-section-heading { margin-bottom: 18px; }
        .profile-section-heading p { margin-top: 4px; color: #64748b; font-size: 13px; line-height: 1.5; }
        .profile-password-heading { margin-top: 28px; padding-top: 24px; border-top: 1px solid #edf1f5; }
        .profile-field { max-width: 100%; }
        .profile-field .custom-label { display: block; margin-bottom: 7px; color: #334155; font-size: 13px; font-weight: 600; }
        .profile-field .input-field { min-height: 44px; border-color: #d6dee7; border-radius: 9px; transition: border-color .15s ease, box-shadow .15s ease; }
        .profile-field .input-field:focus { border-color: #FF6210; outline: 0; box-shadow: 0 0 0 3px rgb(255 98 16 / 15%); }
        .profile-submit { display: flex; align-items: center; gap: 14px; margin-top: 8px; padding-top: 20px !important; }
        .profile-submit .btn-primary { min-height: 44px; padding: 0 22px; border-radius: 9px; transition: transform .15s ease, box-shadow .15s ease; }
        .profile-submit .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 12px rgb(15 23 42 / 14%); }
        .profile-submit .btn-primary:focus-visible, .profile-avatar-upload:focus-within { outline: 3px solid #FFB088; outline-offset: 2px; }
        @media (max-width: 767px) { .profile-grid { gap: 22px !important; padding: 20px !important; } .profile-photo-panel { position: static; min-height: 0; gap: 20px; } .profile-photo-panel > div:last-child { margin-top: 4px; } }
        @media (max-width: 480px) { .profile-grid { padding: 14px !important; } .profile-photo-panel { padding: 16px; } .profile-submit { align-items: stretch; flex-direction: column; } .profile-submit .btn-primary { width: 100%; } }
        @media (prefers-reduced-motion: reduce) { .profile-field .input-field, .profile-submit .btn-primary { transition: none; } .profile-submit .btn-primary:hover { transform: none; } }
    </style>

    
@endsection
