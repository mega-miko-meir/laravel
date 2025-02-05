<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function logout(){
        Auth::logout();
        return redirect('/');
    }

    public function login(Request $request){
        $incomingFields = $request->validate(
            [
                'loginname' => 'required|email',
                'loginpassword' => 'required'
            ]
        );

        if(Auth::attempt(['email' => $incomingFields['loginname'], 'password' => $incomingFields['loginpassword']])){
            $request->session()->regenerate();
        }
        return redirect('/');

    }

    public function register(Request $request){
        $incomingFields = $request->validate(
            [
                'full_name' => 'required|string|max:255',
                'first_name' => 'required',
                'last_name' => 'required',
                'position' => 'nullable',
                'email' => 'required|email|unique:users,email|max:255',
                'password' => 'required|min:8',
            ]
        );

        $incomingFields['password'] = bcrypt($incomingFields['password']);
        $user = User::create($incomingFields);
        Auth::login($user);
        return redirect('/')->with('success', 'Congrats! You are logged in!');
    }


}
