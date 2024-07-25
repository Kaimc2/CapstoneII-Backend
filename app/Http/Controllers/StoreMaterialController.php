<?php

namespace App\Http\Controllers;

use App\Enums\Pagination;
use App\Models\StoreMaterial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreMaterialController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $materials = StoreMaterial::query();

            if ($search) {
                $materials->where('name', 'LIKE', $search)
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
            $material = StoreMaterial::find($id);

            if (!$material) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Material not found'
                ], 404);
            }

            if ($user instanceof User && !$user->can('manage store materials')) {
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
            $rules = [
                'store_id' => 'required|string',
                'material_id' => 'required|string',
                'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/'
            ];
            $inputs = $request->only('store_id', 'material_id', 'price');
            $validator = Validator::make($inputs, $rules);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage store materials')) {
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

            $existedMaterial = StoreMaterial::where('store_id', $request->store_id)->where('material_id', $request->material_id)->first();
            if ($existedMaterial) {
                return response()->json([
                    'status' => 'error',
                    'message' => ["material" => 'Material already added']
                ], 422);
            }

            $material = StoreMaterial::create($inputs);

            return response()->json([
                'status' => 'success',
                'message' => 'Material has been created successfully',
                'data' => $material
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
            $material = StoreMaterial::find($id);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage store materials')) {
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

            $rules = [
                'store_id' => 'required|string',
                'material_id' => 'required|string',
                'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/'
            ];
            $inputs = $request->only('store_id', 'material_id', 'price');
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
            $material = StoreMaterial::find($id);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage store materials')) {
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
