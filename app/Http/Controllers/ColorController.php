<?php

namespace App\Http\Controllers;

use App\Enums\Pagination;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $colors = Color::query();

            if ($search) {
                $colors->where('name', 'LIKE', "%{$search}%")
                    ->orderBy('id', 'ASC')
                    ->paginate($item_per_page);
            }

            $data = $colors
                ->orderBy('name', 'asc')->paginate($item_per_page);

            return response()->json([
                'status' => 'success',
                'data' => ColorResource::collection($data),
                'meta' => [
                    'currentPage' => $data->currentPage(),
                    'from' => $data->firstItem(),
                    'lastPage' => $data->lastPage(),
                    'perPage' => $data->perPage(),
                    'to' => $data->lastItem(),
                    'total' => $data->total(),
                ],
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
            $color = Color::find($id);

            if (!$color) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Color not found'
                ], 404);
            }

            if ($user instanceof User && !$user->can('manage colors')) {
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
                'name' => 'required|string|max:256|unique:colors',
                'hex_code' => 'required|string|max:7|unique:colors'
            ];
            $inputs = $request->only('name', 'hex_code');
            $validator = Validator::make($inputs, $rules);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage colors')) {
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

            $color = Color::create($inputs);

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
            $color = Color::find($id);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage colors')) {
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
                'name' => "required|string|max:256|unique:colors,name,$id",
                'hex_code' => "required|string|max:7|unique:colors,hex_code,$id",
            ];
            $inputs = $request->only('name', 'hex_code');
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
            $color = Color::find($id);
            $user = auth()->user();

            if ($user instanceof User && !$user->can('manage colors')) {
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
