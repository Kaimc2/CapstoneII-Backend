<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', 5);
            $roles = Role::whereAny(['name'], 'LIKE', $search)->paginate($item_per_page);
            return response()->json([
                'status' => 'Success',
                'data' => $roles
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Error catching',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|min:4',
            ];
            $inputs = $request->only('name');
            $validation_errors = Validator::make($inputs, $rules);
            if ($validation_errors->fails()) {
                return response()->json([
                    'status' => ' Error validation',
                    'message' => $validation_errors->messages()->all()
                ]);
            }
            $status = Role::create([
                'name' => $request->input('name'),
                'guard_name' => 'api'
            ]);
            if (!$status) {
                return response()->json([
                    'status' => 'Error creating',
                    'message' => 'Failed to create role'
                ]);
            }
            return response()->json([
                'status' => 'Success',
                'message' => 'Role created successfully'
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'name' => 'required|string|min:4'
            ];
            $inputs = $request->input('name');
            $validation_errors = Validator::make($inputs, $rules);
            if ($validation_errors->fails()) {
                return response()->json([
                    'status' => 'Error validation',
                    'message' => $validation_errors->errors()->all()
                ]);
            }
            $existing_role = Role::findById($id)->first();
            if (!$existing_role) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Data not found!'
                ], 404);
            }
            $status = Role::fill([
                'name' => $request->input('name')
            ])->update();
            if (!$status) {
                return response()->json([
                    'status' => 'Error updating role',
                    'message' => 'Roles not updated'
                ]);
            }
            return response()->json([
                'status' => 'Success',
                'message' => 'Role updated successfully'
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Error catching',
                'message' => $ex->getMessage(),
            ]);
        }
    }
}
