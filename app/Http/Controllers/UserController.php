<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use http\Env\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use Spatie\Permission\Models\Role;
use function Symfony\Component\Translation\t;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', 5);
            $users = User::with('getRole')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', $search)
                        ->whereHas('getRole', function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%$search%");
                        });
                })->paginate($item_per_page);
            // Validate and sanitize input
            $validated = $request->validate([
                'search' => 'nullable|string|max:255',
                'item_per_page' => 'nullable|integer|min:1|max:100'
            ]);
            $search = $validated['search'] ?? '';
            $item_per_page = $validated['item_per_page'] ?? 5;

            $users = User::where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })->paginate($item_per_page);

            return response()->json([
                'status' => 'success',
                'data' => UserResource::collection($users),
                'meta' => [
                    'currentPage' => $users->currentPage(),
                    'from' => $users->firstItem(),
                    'lastPage' => $users->lastPage(),
                    'perPage' => $users->perPage(),
                    'to' => $users->lastItem(),
                    'total' => $users->total(),
                ],
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching',
                'message' => $ex->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|min:6',
                'email' => 'required|string',
                'phone_number' => 'required|string',
                'password' => 'required|string',
            ];
            $inputs = $request->only(['name', 'email', 'phone_number', 'password']);
            $validation_errors = Validator::make($inputs, $rules);
            if ($validation_errors->fails()) {
                return response()->json([
                    'status' => 'error validation',
                    'message' =>  $validation_errors->messages()->all()
                ]);
            }
            $user = User::create($inputs);
            if (!$user) {
                return response()->json([
                    'status' => 'error creating user',
                    'message' => 'Failed creating user'
                ]);
            }
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $profile_picture_name = 'user_profile_' . $user->id . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('profile_pictures', $profile_picture_name, 'public');
                if (!$path) {
                    return response()->json([
                        'status' => 'error storing image',
                        'message' => 'Failed storing image'
                    ]);
                }
                $user->update(['profile_picture' => $path]);
            }
            return response()->json([
                'path_image' => $path,
                'status' => 'success',
                'message' => 'User created successfully'
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching',
                'message' => $ex->getMessage()
            ]);
        }
    }

    public  function update(Request $request, $id)
    {
        try {
            $rules = [
                'name' => 'required|string|min:6',
                'email' => 'required|string',
                'phone_number' => 'required|string',
                'password' => 'required|string',
            ];
            $inputs = $request->only(['email', 'password', 'phone_number', 'password', 'profile_picture']);
            $validation_errors = Validator::make($inputs, $rules);
            if ($validation_errors->fails()) {
                return response()->json([
                    'status' => 'error validation',
                    'message' =>  $validation_errors->messages()->all()
                ]);
            }
            $status = User::create($inputs);
            if (!$status) {
                return response()->json([
                    'status' => 'error creating user',
                    'message' => 'Failed creating user'
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully'
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching',
                'message' => $ex->getMessage()
            ]);
        }
    }

    public function assign_new_role(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $rules = ['role' => 'required|string'];
        $inputs = $request->only('role');
        $validation_errors = Validator::make($inputs, $rules);

        if ($validation_errors->fails()) {
            return response()->json([
                'status' => 'error validation',
                'message' => $validation_errors->errors()->all()
            ], 403);
        }

        $user->syncRoles([$request->role]);

        return response()->json([
            'status' => 'success',
            'message' => 'Role changed successfully'
        ]);
    }

    public function display($id)
    {
        try {
            $user = User::find($id);
            $path = $user->profile_picture;
            if (Storage::disk('public')->exists($path)) {
                return response()->file(storage_path(`app/public/$path`));
            }
            return response()->json([
                'status' => 'error',
                'message' => 'profile picture not found'
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching',
                'message' => $ex->getMessage()
            ]);
        }
    }
}
