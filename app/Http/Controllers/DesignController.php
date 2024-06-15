<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enums\Pagination;

class DesignController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', 10);
            $designs = Design::query();

            if ($search) {
                $designs->where(function($query) use ($search) {
                    $query->where('name', 'LIKE', "%$search%")
                          ->orWhere('content', 'LIKE', "%$search%")
                          ->orWhere('status', 'LIKE', "%$search%");
                });
            }

            $data = $designs->orderBy('name', 'asc')->paginate($item_per_page);

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

    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:256',
                'deleted' => 'required|boolean',
                'content' => 'nullable|string',
                'status' => 'nullable|string'
            ];

            $inputs = $request->only('name', 'deleted', 'content', 'status');
            $validator = Validator::make($inputs, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $design = Design::create($inputs);

            return response()->json([
                'status' => 'success',
                'message' => 'Design has been created successfully',
                'data' => $design
            ], 201);
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
            $design = Design::find($id);

            if (!$design || $design->deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Design not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $design
            ], 200);
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
            $design = Design::find($id);

            if (!$design || $design->deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Design not found'
                ], 404);
            }

            $rules = [
                'name' => 'required|string|max:256',
                'deleted' => 'required|boolean',
                'content' => 'nullable|string',
                'status' => 'nullable|string',
            ];

            $inputs = $request->only('name', 'deleted', 'content', 'status');
            $validator = Validator::make($inputs, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $design->update($inputs);

            return response()->json([
                'status' => 'success',
                'message' => 'Design has been updated successfully',
                'data' => $design
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
            $design = Design::find($id);

            if (!$design || $design->deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Design not found'
                ], 404);
            }

            $design->deleted = true;
            $design->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Design deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
