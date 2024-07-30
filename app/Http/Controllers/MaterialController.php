<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\Pagination;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', 10);
            $materials = Material::query();

            if ($search) {
                $materials->where('name', 'LIKE', "%{$search}%")
                    ->orderBy('id', 'ASC')
                    ->paginate($item_per_page);
            }

            $data = $materials
                ->orderBy('name', 'asc')->paginate($item_per_page);

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'message' => 'Data has been retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function show($id)
    {
        try {
            $user = auth()->user();
            $material = Material::find($id);

            if (!$material) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Material not found'
                ], 404);
            }

            if ($user instanceof User && !$user->can('manage materials')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $material
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = ['name' => 'required|string|max:256|unique:materials'];
            $inputs = $request->only('name');
            $validator = Validator::make($inputs, $rules);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage materials')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $design = Material::create($inputs);

            return response()->json([
                'status' => 'success',
                'message' => 'Material has been created successfully',
                'data' => $design
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $material = Material::find($id);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage materials')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (!$material) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Material not found'
                ], 404);
            }

            $rules = ['name' => `required|string|max:256|unique:materials,name,$id`];
            $inputs = $request->only('name');
            $validator = Validator::make($inputs, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $material->update($inputs);

            return response()->json([
                'status' => 'success',
                'message' => 'Material has been updated successfully',
                'data' => $material
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $material = Material::find($id);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage materials')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (!$material) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Material not found'
                ], 404);
            }

            $material->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Material deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
