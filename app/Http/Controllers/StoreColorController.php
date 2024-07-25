<?php

namespace App\Http\Controllers;

use App\Enums\Pagination;
use App\Models\StoreColor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreColorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $colors = StoreColor::query();

            if ($search) {
                $colors->where('name', 'LIKE', $search)
                    ->orderBy('id', 'ASC')
                    ->paginate($item_per_page);
            }

            $data = $colors
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
            $color = StoreColor::find($id);

            if (!$color) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Color not found'
                ], 404);
            }

            if ($user instanceof User && !$user->can('manage store colors')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $color
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
                'color_id' => 'required|string',
                'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/'
            ];
            $inputs = $request->only('store_id', 'color_id', 'price');
            $validator = Validator::make($inputs, $rules);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage store colors')) {
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

            $existedColor = StoreColor::where('store_id', $request->store_id)->where('color_id', $request->color_id)->first();
            if ($existedColor) {
                return response()->json([
                    'status' => 'error',
                    'message' => ["color" => 'Color already added']
                ], 422);
            }

            $color = StoreColor::create($inputs);

            return response()->json([
                'status' => 'success',
                'message' => 'Color has been created successfully',
                'data' => $color
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
            $color = StoreColor::find($id);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage store colors')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (!$color) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Color not found'
                ], 404);
            }

            $rules = [
                'store_id' => 'required|string',
                'color_id' => 'required|string',
                'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/'
            ];
            $inputs = $request->only('store_id', 'color_id', 'price');
            $validator = Validator::make($inputs, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $color->update($inputs);

            return response()->json([
                'status' => 'success',
                'message' => 'Color has been updated successfully',
                'data' => $color
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
            $color = StoreColor::find($id);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage store colors')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (!$color) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Color not found'
                ], 404);
            }

            $color->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Color deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
