<?php

namespace App\Http\Controllers;

use App\Mail\VerifyingEmail;
use App\Mail\MyMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Contracts\Providers\Storage;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ];
            $inputs = $request->only('email', 'password', 'name', 'password_confirmation');
            $validation_error = Validator::make($inputs, $rules);
            if ($validation_error->fails()) {
                return response()->json([
                    'message' => $validation_error->errors()->all(),
                    'access' => false
                ], 422);
            }
            $check_existing_email = DB::table('users')
                ->where('email', $request->input('email'))
                ->exists();
            if ($check_existing_email) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This email address has already been used'
                ]);
            }
            Mail::send(new VerifyingEmail(
                $request->input('email'),
                $request->input('password'),
                $request->input('name')
            ));
            return response()->json([
                'status' => 'success',
                'message' => 'Verification email sent successfully',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function verify_email(Request $request)
    {
        try {
            $status_user_registration = User::create([
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'name' => $request->input('name'),
            ]);
            if (!$status_user_registration) {
                return response()->json([
                    'status' => 'error verification',
                    'message' => 'Registration failed'
                ]);
            }
            return response()->json([
                'status' => 'success verification',
                'message' => 'Registration successful'
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching exception',
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
            $user = User::where('email', $credentials['email'])->first();

            if ($user) {
                $attempts = $user->login_attempts;
                $lastAttemptTime = $user->last_login_attempt_at;
                $lockoutTime = now()->subMinutes(15);

                if ($attempts >= 3 && $lastAttemptTime > $lockoutTime) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many login attempts. Please try again later.'
                    ], 429);
                }

                if (!$token = JWTAuth::attempt($credentials)) {
                    $user->login_attempts = $attempts + 1;
                    $user->last_login_attempt_at = now();
                    $user->save();

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid credentials'
                    ], 401);
                }

                $user->login_attempts = 0;
                $user->last_login_attempt_at = null;
                $user->save();

                $auth = User::where('id', $user->id)->first();
                $auth->accessToken = $token;
                $authentication = $auth;

                return response()->json([
                    'message' => 'You are logged in successfully',
                    'status' => 'success',
                    'data' => $authentication
                ], 200, [], JSON_NUMERIC_CHECK);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function me()
    {
        $user = auth()->user();
        $Roles = $user->getRoleNames();
        $user->Role = $Roles;

        $Permissions = $user->getPermissionNames();
        $user->Permissions = $Permissions;

        $my_info = $user->makeHidden('permissions', 'roles')->toArray();

        return response()->json($my_info);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function forgot_password(Request $request)
    {
        try {
            $rule = [
                'email' => 'required|string',
            ];
            $validation_error = Validator::make($request->all(), $rule);
            if ($validation_error->fails()) {
                return response()->json([
                    'status' => 'error validation',
                    'message' => $validation_error->errors()->all(),
                    'email' => $request->email
                ], 403);
            } else {
                $data = User::where('email', $request->email)->first();
                if ($data) {
                    $resetPasswordInfo = [
                        'email' => $data->email,
                        'token' => Str::random(20),
                    ];
                    $status = DB::table('password_reset_tokens')->where('email', $resetPasswordInfo['email'])->exists();
                    Mail::send(new MyMail($resetPasswordInfo['token'], $resetPasswordInfo['email']));
                    if ($status) {
                        DB::table('password_reset_tokens')->where('email', $resetPasswordInfo['email'])->update($resetPasswordInfo);
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Email has been sent successfully',
                            'data' => DB::table('password_reset_tokens')->where('email', $resetPasswordInfo['email'])->first()
                        ], 200);
                    } else {
                        DB::table('password_reset_tokens')->insert($resetPasswordInfo);
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Email has been sent successfully',
                            'data' => DB::table('password_reset_tokens')->where('email', $resetPasswordInfo['email'])->first()
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Email not found!'
                    ], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function reset_password(Request $request)
    {
        try {
            $rules = [
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required|string',
            ];
            $input = $request->only('password', 'password_confirmation');

            $validation_errors = Validator::make($input, $rules);
            if ($validation_errors->fails()) {
                return response()->json([
                    'status' => 'error validating',
                    'message' => $validation_errors->errors()->all()
                ]);
            } else {
                $token = DB::table('password_reset_tokens')->where('token', $request->token)->first();
                if ($token) {
                    $user = User::where('email', $token->email)->first();
                    if (Hash::check($request->password, $user->password)) {
                        return response()->json([
                            'status' => '410',
                            'message' => 'Please create a new password different from the previous one'
                        ]);
                    } else {
                        $user->password = Hash::make($request->password);
                        $status = $user->save();
                        return response()->json([
                            'status' => '200',
                            'message' => 'Your password has been changed',
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => '400',
                        'message' => 'Invalid token',
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => $e->getMessage()
            ]);
        }
    }
}
