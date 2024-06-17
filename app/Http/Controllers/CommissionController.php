<?php

namespace App\Http\Controllers;

use App\Enums\Pagination;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $designs = Commission::where('name', 'LIKE', "%$search%")
                ->where('deleted', false)
                ->latest('created_at')
                ->paginate($item_per_page);

            return response()->json([
                'status' => 'Success',
                'data' => $designs
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|min:4',
                'front_content' => 'required|string',
                'back_content' => 'required|string'
            ];

            $validation = Validator::make($request->all(), $rules);

            if ($validation->fails()) {
                return response()->json([
                    'status' => 'Error',
                    'message' => $validation->errors()->all()
                ], 422);
            }

            $design = Commission::create($request->only(['name', 'front_content', 'back_content']));

            return response()->json([
                'status' => 'Success',
                'message' => 'Design created successfully',
                'data' => $design
            ], 201);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $design = Commission::find($id);

            if (!$design || $design->deleted) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Design not found!'
                ], 404);
            }

            return response()->json([
                'status' => 'Success',
                'data' => $design
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
}
