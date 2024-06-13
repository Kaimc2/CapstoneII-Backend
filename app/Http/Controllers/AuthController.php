<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Contracts\Providers\Storage;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone_number' => 'required|string',
                'password' => 'required|string|min:6|confirmed',
            ]);
            if ($validated->fails()) {
                return response([
                    "message" => $validated->errors()->all(),
                    'access' => false
                ], 422);
            }
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone_number' => $request->input('phone_number'),
                'password' => Hash::make($request->input('password'),),
            ]);
            return response()->json([
                'access' => true,
                'message' => 'User created.',
                'user' => $user,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    public function login(Request $request)
    {
        try {
            $rules = [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error validation',
                    'message' => $validator->errors()->all()
                ], 400);
            }

            $credentials = $request->only(['email', 'password']);
            $token = JWTAuth::attempt($credentials);
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }
            JWTAuth::setToken($token);
            $user = JWTAuth::user();
            $auth = User::where('id', $user->id)->first();
            $auth->accessToken = $token;
            $authentication = $auth;

            return response()->json([
                'message' => 'You are login successfully',
                'status' => 'success',
                'data' => $authentication
            ], 200, [], JSON_NUMERIC_CHECK);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
}
