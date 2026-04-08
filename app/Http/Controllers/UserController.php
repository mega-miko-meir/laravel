<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function logout(){
        Auth::logout();
        return redirect('/');
    }

    public function login(UserLoginRequest $request){
        $credentials = $request->validated();

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

    // dd(Role::all());


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
        $roles = Role::all(); // Получаем все роли (admin, editor, viewer)
        return view('components/registration', compact('user', 'roles'));
    }

    public function register(UserStoreRequest $request){
        $incomingFields = $request->validated();

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
            $user = User::with('role')->findOrFail($id);

            return view('show-user', compact('user'));
        }

        public function destroy($id){
            $user = User::findOrFail($id);
            $user->delete();

            return redirect('/users')->with('success', 'The user deleted successfully!');
        }

    public function showEdit(User $user){
        // dd(Role::all());
        return view('components/user-edit-form', [
            // 'action' => url("/edit/$user->id"),
            // 'method' => 'PUT',
            'user' => $user
        ]);
    }

    public function update(UserUpdateRequest $request, $userId)
    {
        $user = User::findOrFail($userId);
        $incomingFields = $request->validated();

        // Если пароль пустой — не трогаем
        if (empty($incomingFields['password'])) {
            unset($incomingFields['password']);
        } else {
            $incomingFields['password'] = bcrypt($incomingFields['password']);
        }



        $user->update($incomingFields);

        return redirect('/users')
            ->with('success', 'Пользователь успешно обновлён!');
    }


}
