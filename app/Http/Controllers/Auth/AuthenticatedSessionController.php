<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthenticatedSessionController extends Controller {
    public function create() { return view('auth.login'); }
    public function store(Request $request) {
        $credentials = $request->validate(['email'=>['required','email'],'password'=>['required','string']]);
        if (!Auth::attempt($credentials, $request->boolean('remember'))) return back()->withErrors(['email'=>'Email atau password tidak sesuai.'])->onlyInput('email');
        $request->session()->regenerate();

        // Admins land in the admin dashboard; regular attendees land on the public homepage.
        return $request->user()->is_admin
            ? redirect()->route('admin.dashboard')
            : redirect()->route('home');
    }
    public function destroy(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('home'); }
}
