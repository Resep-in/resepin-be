<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function editProfile(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Update the user's profile
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($request->hasFile('image')) {
            if ($user->getAttribute('profile_image') && $user->getAttribute('profile_image') !== 'default/profile/default.png') {
                Storage::disk('public')->delete($user->getAttribute('profile_image'));
            }

            $imagePath = Storage::disk('public')->putFile('image/profile', $request->file('image'));
            $request->merge(['profile_image' => $imagePath]);
        }
        try {
            User::where('id', $user->getAttribute('id'))->update(
                $request->only('name', 'email', 'profile_image')
            );


            $user = $user->fresh()->append('profile_url'); // Refresh the user instance to get the updated data

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update profile'], 500);
        }

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // check if the current password is correct
        if (!Hash::check($request->input('current_password'), $user->getAttribute('password'))) {
            return response()->json(['message' => 'Current password is incorrect'], 401);
        }

        // Update the user's password
        $password = Hash::make($request->input('new_password'));
        $request->merge(['password' => $password]);

        try {
            User::where('id', $user->getAttribute('id'))->update(
                $request->only('password')
            );
            $user = $user->fresh()->append('profile_url'); // Refresh the user instance to get the updated data
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to change password'], 500);
        }

        return response()->json(['message' => 'Password changed successfully', 'user' => $user]);
    }
}
