<?php

namespace App\Http\Controllers;

use App\Events\StatusNotification;
use App\Http\Resources\UserResource;
use App\Mail\MyMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;

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

            // Validate user input
            $inputs = $request->only('name', 'email', 'password', 'password_confirmation');
            $validation_error = Validator::make($inputs, $rules);
            if ($validation_error->fails()) {
                return response()->json([
                    'message' => $validation_error->errors()->all(),
                    'access' => false
                ], 422);
            }

            // Check if email already exist
            $check_existing_email = DB::table('users')
                ->where('email', $request->input('email'))
                ->exists();
            if ($check_existing_email) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This email address has already been used'
                ]);
            }

            // Create a new user
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone_number' => $request->input('phone_number'),
                'password' => Hash::make($request->input('password')),
            ]);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create user'
                ]);
            }

            // Send verification email
            event(new Registered($user));
            $user->assignRole('designer');

            // Authenticate user
            $token = JWTAuth::fromUser($user);
            $user->accessToken = $token;

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => new UserResource($user),
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'exception error',
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

                return response()->json([
                    'status' => 'success',
                    'message' => 'You are logged in successfully',
                    'data' => new UserResource($auth),
                ], 200, [], JSON_NUMERIC_CHECK);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $ex) {
            return response()->json(['message' => 'Failed to log out, please try again.'], 500);
        }
    }

    public function me()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['message' => 'User not found']);
            }

            $roles = $user->getRoleNames();
            $permissions = $user->getPermissionNames();

            $user->roles = $roles;
            $user->permissions = $permissions;

            // $my_info = $user->makeHidden(['permissions', 'roles'])->toArray();
            $my_info = $user->makeHidden(['permissions', 'roles']);

            return response()->json(['data' => new UserResource($my_info), 'roles' => $roles]);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Could not retrieve user information',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function verify_email(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);

            if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
                event(new StatusNotification('Invalid verification link', 'error'));
                return redirect(env('FRONTEND_URL') . 'account/verify');
            }

            if ($user->hasVerifiedEmail()) {
                event(new StatusNotification('Email already verified', 'normal'));
                return redirect(env('FRONTEND_URL'));
            }

            $user->markEmailAsVerified();
            event(new StatusNotification('Registration successful', 'success'));

            return redirect(env('FRONTEND_URL'))->with([
                'status' => 'success',
                'message' => 'Registration successful'
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function resend_email(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Verification link sent!'
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
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
