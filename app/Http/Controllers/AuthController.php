<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login-student');
    }

    public function showAdminLogin()
    {
        return view('auth.login-admin');
    }

    public function showTeacherLogin()
    {
        return view('auth.login-teacher');
    }

    public function showStudentLogin()
    {
        return view('auth.login-student');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        return redirect()->intended(match($user->role) {
            'admin'   => route('admin.dashboard'),
            'teacher' => route('teacher.dashboard'),
            'student' => route('student.dashboard'),
            default   => route('student.login'),
        });
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('student.login');
    }
}