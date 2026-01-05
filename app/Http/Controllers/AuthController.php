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
        $password       = $request->input('password');
        $email          = $request->input('email');
        $tahun          = $request->input('tahun');
        $is_active      = 'Aktif';

        if(Auth::guard('web')->attempt(['email' => $email, 'password' => $password, 'is_active' => $is_active, 'tahun' => $tahun]))
        {
            return redirect('/home')->with('success', 'Login Berhasil');
        }
        // elseif(Auth::guard('web')->attempt(['tahun' => $tahun]))
        // {
        //     return redirect('/')->with('error', 'Akun Tidak Ditemukan');
        // }
        else 
        {
            return redirect('/login')->with('error', 'Upss.. Akun Anda belum aktif atau Email & Password Salah');
        }
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
