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
        // $user = Auth::user();
        // $token = $user->createToken('main')->plainTextToken;

        return redirect('/dashboard');
    }

    // if (Auth::attempt(['email' => $credentials['loginname'], 'password' => $credentials['loginpassword']])) {
    //     $request->session()->regenerate();
    //     return redirect()->intended('/dashboard'); // или куда нужно
    // }


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

    public function showRegister($user = null){
        return view('Components/registration', compact('user'));
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
        // Auth::login($user);

        // return redirect('/')->with('success', 'Congrats! You are logged in!');
        return redirect('/users')->with('success', 'Congrats! You created a new user!');
    }

    public function index(){

        $users = User::all();
        return view('users', compact('users'));
        // return UserResource::collection(User::with('role')->paginate(100));
    }

    public function show($id){
        $user = User::findOrFail($id);

        return view('show-user', compact('user'));
    }

    public function destroy($id){
        $user = User::findOrFail($id);
        $user->delete();

        return redirect('/users')->with('success', 'The user deleted successfully!');
    }

    public function showEdit(User $user){
        return view('Components/user-edit-form', [
            // 'action' => url("/edit/$user->id"),
            // 'method' => 'PUT',
            'user' => $user
        ]);
    }

    public function update(Request $request, $user){
        $incomingFields = $request->only('full_name', 'first_name', 'last_name', 'position', 'email', 'password');

        // $user = User::findOrFail($user->id);

        $user->update($incomingFields);

        return redirect('/users')->with('success', 'The user updated successfully!');

    }

}
