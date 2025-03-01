<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Register a new user and return an API token
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user)); // Sends verification email

        return response()->json(['message' => 'Account created! Please check your email for verification.'], 201);
    }

    // Login an existing user and return an API token
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Auth::attempt($credentials)) {
            return response()->json(['errors' => ['general' => 'Invalid email or password']], 422);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['errors' => ['general' => 'Please verify your email before logging in']], 403);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('authToken')->plainTextToken,
        ]);
    }
}
