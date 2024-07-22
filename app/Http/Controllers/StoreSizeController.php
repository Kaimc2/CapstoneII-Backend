<?php

namespace App\Http\Controllers;

use App\Enums\Pagination;
use App\Models\StoreSize;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreSizeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $sizes = StoreSize::query();

            if ($search) {
                $sizes->where('name', 'LIKE', $search)
                    ->orderBy('id', 'ASC')
                    ->paginate($item_per_page);
            }

            $data = $sizes
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
            $size = StoreSize::find($id);

            if (!$size) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Size not found'
                ], 404);
            }

            if ($user instanceof User && !$user->can('manage store sizes')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $size
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
                'size_id' => 'required|string',
                'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/'
            ];
            $inputs = $request->only('store_id', 'size_id', 'price');
            $validator = Validator::make($inputs, $rules);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage store sizes')) {
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

            $size = StoreSize::create($inputs);

            return response()->json([
                'status' => 'success',
                'message' => 'Size has been created successfully',
                'data' => $size
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
            $size = StoreSize::find($id);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage store sizes')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (!$size) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Size not found'
                ], 404);
            }

            $rules = [
                'store_id' => 'required|string',
                'size_id' => 'required|string',
                'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/'
            ];
            $inputs = $request->only('store_id', 'size_id', 'price');
            $validator = Validator::make($inputs, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $size->update($inputs);

            return response()->json([
                'status' => 'success',
                'message' => 'Size has been updated successfully',
                'data' => $size
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
            $size = StoreSize::find($id);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage store sizes')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (!$size) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Size not found'
                ], 404);
            }

            $size->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Size deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
