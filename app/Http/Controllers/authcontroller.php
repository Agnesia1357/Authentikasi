<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class authcontroller extends Controller
{
    public function register(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'email' => 'required|max:255|unique:users,email',
                'password' => 'required',
            ]);
            if ($validate->fails()) {
                return response()->json($validate->errors());
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'msg' => 'Anda Berhasil Register',
                'data' => $user,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'msg' => $th->getMessage()
            ]);
        }
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'msg' => 'Unauthorized'
            ], 401);
        }
        $user = User::where('email', $request->email)->firstOrFail();


        $token = $user->createToken('apiToken')->plainTextToken;

        return response()->json([
            'msg' => 'Login Success',
            'data' => $user,
            'akses token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        // Auth::user()->tokens()->delete();p
        // return response()->json([
        //     'message' => 'logout success'
        // ]);
        // if ($response->getStatusCode() === 500) {
        //     return response()->json(['message' => 'Tidak ada pengguna yang terotentikasi'], 401);
        // } else {
        //     Auth::user()->tokens->each(function ($token, $key) {
        //         $token->delete();
        //     });
        //     // Auth::user()->tokens()->delete();

        //     // return response()->json([
        //     //     'message' => 'logout success'
        //     // ]);

        //     return response()->json(['message' => 'Logout berhasil'], 200);
        // }
        try {
            $user = $request->user();

            // Mencabut semua token otentikasi pengguna
            $user->tokens->each(function ($token, $key) {
                $token->delete();
            });

            return response()->json(['message' => 'Logout berhasil'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan internal saat logout'], 500);
        }
    }
    public function index()
    {
        $user = User::all();
        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

}

