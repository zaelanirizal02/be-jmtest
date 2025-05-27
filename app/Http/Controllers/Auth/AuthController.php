<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:pendaftaran,dokter,perawat,apoteker,superadmin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek apakah request memiliki header Authorization
        $user = auth('sanctum')->user();

        // Hanya superadmin yang bisa membuat user superadmin baru
        if ($request->role === 'superadmin' && (!$user || $user->role !== 'superadmin')) {
            return response()->json([
                'message' => 'Unauthorized. Hanya superadmin yang dapat membuat akun superadmin baru.',
                'current_user' => $user ? $user->role : 'not logged in'
            ], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required']
        ]);

        // Cek kredensial
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Ambil user yang berhasil login
        $user = Auth::user();

        // Hapus token lama jika ada
        $user->tokens()->delete();

        // Buat token baru
        $plainTextToken = $user->createToken('auth_token')->plainTextToken;

        //pisah token
        [$id, $token] = explode('|', $plainTextToken, 2);

        // Kirim response dengan token dan user
        return response()->json([
            'id'=> $id,
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang aktif
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    // Tambah method untuk cek status autentikasi
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'is_authenticated' => auth('sanctum')->check()
        ]);
    }
}
