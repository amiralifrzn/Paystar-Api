<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register()
    {
        $fields = request()->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'phone' => $fields['phone'],
            'password' => bcrypt($fields['password']),
        ]);

        $token = $user->createToken('pstoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        event(new Registered($user));
        return response($response, 201);
    }

    public function login()
    {
        $fields = request()->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $login = $fields['email'];

        if(is_numeric($login)){
            $field = 'phone';
        } elseif (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = 'name';
        }

        request()->merge([$field => $login]);
        if(auth()->attempt(array($field => $fields['email'], 'password' => $fields['password'])))
        {
            $token = auth()->user()->createToken('pstoken')->plainTextToken;
        }else{
            return response([
                'msg' => 'wrong username or password!'
            ],401);
        }

        $response = [
            'user' => auth()->user(),
            'token' => $token
        ];

        return response($response, 201);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'msg' => 'logged out!'
        ];
    }

    public function verify($id, $hash)
    {
        $user = User::find($id);
        abort_if(!$user, 403);
        abort_if(!hash_equals($hash, sha1($user->getEmailForVerification())), 403);

        if(!$user->hasVerifiedEmail()){
            $user->markEmailAsVerified();
            event(new Verified($user));
        }
        return view('verified_account');
    }

    public function resendNotification(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    }
}
