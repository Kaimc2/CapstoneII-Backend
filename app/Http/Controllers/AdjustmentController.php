<?php

namespace App\Http\Controllers;

use App\Enums\Pagination;
use App\Http\Controllers\Controller;
use App\Models\Adjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdjustmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $designs = Adjustment::query();

            if ($search) {
                $designs->whereAny(['message'], 'LIKE', $search)
                    ->orderBy('id', 'ASC')
                    ->paginate($item_per_page);
            }

            $data = $designs->orderBy('id', 'asc')->paginate($item_per_page);

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
                'commission_id' => 'required|string|max:256',
                'adjust_date' => 'required|date',
                'duration' => 'nullable|string',
                'message' => 'nullable|string'
            ];

            $inputs = $request->only('name', 'deleted', 'content', 'status');
            $validator = Validator::make($inputs, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $design = Adjustment::create($inputs);

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
            $design = Adjustment::find($id);

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
            $design = Adjustment::find($id);

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
            $design = Adjustment::find($id);

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
