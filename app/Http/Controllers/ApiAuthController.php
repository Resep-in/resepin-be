<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $hashedPassword = $user ? $user->password : null;

        if (!Hash::check($request->input('password'), $hashedPassword)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $return_value = [
            'message' => 'Login successful',
            'user' => $user, // Optionally return user data
            'token' => $user->createToken('resepin')->plainTextToken, // Uncomment if you want to return a token
        ];

        return response()->json($return_value);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
