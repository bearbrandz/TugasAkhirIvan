<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LogActivity;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function username()
    {
        return 'username';
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        LogActivity::catat('login', 'Auth', 'User ' . $user->nama . ' (' . $user->tipe_user . ') berhasil login.');
    }

    protected function loggedOut(Request $request)
    {
        // User sudah logout, catat sebelum session dihapus tidak memungkinkan
        // Logging sudah dilakukan di logout route via web.php
    }
}
