<?php

namespace App\Http\Controllers;

use App\Enums\Pagination;
use Illuminate\Http\Request;
use App\Models\Design;
use Illuminate\Support\Facades\Validator;

class DesignController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $designs = Design::where('name', 'LIKE', "%$search%")
                ->where('deleted', false)
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

            $design = Design::create($request->only(['name', 'front_content', 'back_content']));

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
            $design = Design::find($id);

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

    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'name' => 'sometimes|required|string|min:4',
                'front_content' => 'sometimes|required|string',
                'back_content' => 'sometimes|required|string'
            ];

            $validation = Validator::make($request->all(), $rules);

            if ($validation->fails()) {
                return response()->json([
                    'status' => 'Error',
                    'message' => $validation->errors()->all()
                ], 422);
            }

            $design = Design::find($id);

            if (!$design || $design->deleted) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Design not found!'
                ], 404);
            }

            $design->update($request->only(['name', 'front_content', 'back_content']));

            return response()->json([
                'status' => 'Success',
                'message' => 'Design updated successfully',
                'data' => $design
            ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $design = Design::find($id);

            if (!$design || $design->deleted) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Design not found!'
                ], 404);
            }

            $design->deleted = true;
            $design->save();

            return response()->json([
                'status' => 'Success',
                'message' => 'Design deleted successfully'
            ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
}
