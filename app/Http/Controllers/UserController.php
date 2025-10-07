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

        $credentials = $request->validate([
        'loginname' => ['required', 'email'],
        'loginpassword' => ['required'],
    ]);

    if (Auth::attempt(['email' => $credentials['loginname'], 'password' => $credentials['loginpassword']])) {
        $request->session()->regenerate();
        $user = Auth::user();
        $token = $user->createToken('main')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    return response()->json([
        'message' => 'The provided credentials are incorrect.',
    ], 422);

        // $incomingFields = $request->validate(
        //     [
        //         'loginname' => 'required|email',
        //         'loginpassword' => 'required'
        //     ]
        // );

        // if(Auth::attempt(['email' => $incomingFields['loginname'], 'password' => $incomingFields['loginpassword']])){
        //     $request->session()->regenerate();
        // }
        // $user = Auth::user();
        // $token = $user->createToken('main')->plainTextToken;
        // return response(compact('user', 'token'));

        // return redirect('/');

    }

    public function register(Request $request){
        $incomingFields = $request->validate(
            [
                'full_name' => 'required|string|max:255',
                'first_name' => 'required',
                'last_name' => 'required',
                'position' => 'nullable',
                'email' => 'required|email|unique:users,email|max:255',
                'password' => 'required|min:8|confirmed',
            ]
        );

        if ($request->password !== $request->password_confirmation) {
            return redirect()->back()
                ->withInput() // Возвращаем введённые данные
                ->with('error', 'Пароли не совпадают.');
        }

        $incomingFields['password'] = bcrypt($incomingFields['password']);
        $user = User::create($incomingFields);
        Auth::login($user);

        return redirect('/')->with('success', 'Congrats! You are logged in!');
    }


}
