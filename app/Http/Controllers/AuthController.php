<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login ()
    {
        if(Auth::user())
        {
            return redirect('/home');
        }

        $data = array(
            'title' => 'Login'
        );

        return view('Auth.Login', $data);
    }

    public function cek_login(Request $request)
    {
        $credentials = [
            'email'     => $request->email,
            'password'  => $request->password,
            'is_active' => 'Aktif',
            'tahun'     => $request->tahun,
        ];

        if (Auth::guard('web')->attempt($credentials)) {

            return response()->json([
                'status'   => 'success',
                'message'  => 'Login berhasil',
                'redirect' => url('/home')
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Upss.. Akun Anda belum aktif atau Email & Password Salah'
        ], 401);
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return redirect('/login')->with('success', 'Logout Berhasil');
    }

    public function register ()
    {
        $data = array(
            'title' => 'Halaman Register'
        );

        return view('Auth.Register', $data);
    }
}
