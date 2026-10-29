<?php

namespace Modules\Admin\Http\Controllers;

use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::guard(\Illuminate\Support\Facades\Auth::getDefaultDriver())->user();
        return view('admin::pages.profile.edit', compact('user'));
    }


    public function update(Request $request)
    {
        $user = Auth::guard(\Illuminate\Support\Facades\Auth::getDefaultDriver())->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:60',
            'password' => ['nullable', 'string', 'confirmed', new StrongPassword()],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }


        $user->name = $request->name;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            fn_upload_image($request->file('image'), 'avatar', $user->id, true);
        }
        $user->save();

        $message = 'Account details updated successfully.';
        return response()->json([
            'success' => true,
            'message' => $message,
            'redirect_url' => route('profile.edit')
        ]);
    }
}
