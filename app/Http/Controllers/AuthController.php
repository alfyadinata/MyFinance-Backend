<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use App\Finance;

class AuthController extends Controller
{
    public function sign(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials','code' => 400]);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => $e, 'code' => 500]);
        }

        return response()->json([
            'code' => 200,
            'user' => Auth::user(),
            'token' =>  $token,
        ]);
    }

    public function profile()
    {
        try {
            $user       =   Auth::user();
            $income     =   Finance::where('user_id',$user->id)->where('income',1)->sum('price');
            $spending   =   Finance::where('user_id',$user->id)->where('income',2)->sum('price');
            $total      =   Finance::where('user_id',$user->id)->sum('price');

            return response()->json([
                'code'      =>  200,
                'data'      => $user,
                'income'    => $income,
                'total'     =>  $total,
                'spending'  =>  $spending
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'mssg' =>  $th,
            ]);
        }
    }

    public function signUp(Request $request)
    {
        try {
            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
            ]);
            
            if ($user) {
                return response()->json([
                    'code'      => 200,
                    'status'    => 'ok',
                    'data'      => $user
                ]);
            }
        } catch (\Throwable $err) {
            return response()->json([
                'code'      => 500,
                'mssg'      => $err
            ]);
        }
    }

    public function signOut()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'code'  =>  200,
                'mssg'  => 'success logout'
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                'code'      => 500,
                'mssg'      => $err
            ]);
        }
    }
}
