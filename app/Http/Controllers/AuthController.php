<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $request = $request->validated();
        $request['password'] =  bcrypt($request['password']);
        $user = User::create($request);
        return response()->json([$user]);
    }

    public function user()
    {
    }

    // public function login(LoginRequest $request)
    // {
    //     if (!auth()->attempt($request->validated())) {
    //         occuredErrorInApi('There is no such user.');
    //     }
    //     $user = auth()->user();
    //     $user['token'] = $user->createToken('token_base_name')->plainTextToken;
    //     $user['token_type'] = 'Bearer';
    //     return $user;
    // }
}
