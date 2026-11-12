<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
class RegisteredUserController extends Controller {
    public function create() { return view('auth.register'); }
    public function store(Request $request) {
        $data = $request->validate(['name'=>['required','string','max:255'],'email'=>['required','email','max:255','unique:users'],'phone'=>['nullable','string','max:30'],'password'=>['required','confirmed',Rules\Password::defaults()]]);
        $user = User::create($data); Auth::login($user); $request->session()->regenerate();
        return redirect()->route('ticket-war');
    }
}
