<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DesignController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', 10);
            $designs = Design::query();

            $data = $designs->whereAny(['name', 'content', 'status'], 'LIKE', $search)
                ->orderBy('name', 'asc')
                ->paginate($item_per_page);
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'message' => 'Data has been retrieved successfully'
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'status' => 'error catching exception',
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:256',
                'deleted' => 'required',
                'content' => 'nullable|string',
                'status' => 'nullable|string'
            ];
            $inputs = $request->only('name', 'deleted', 'content', 'status');
            $validation_errors = Validator::make($inputs, $rules);
            if ($validation_errors->fails())
            {
                return response()->json([
                    'status' => 'error validation',
                    'message' => $validation_errors->errors()->all()
                ], 403);
            }
            $status = Design::create([
                'name' => $request->input('name'),
                'content' => $request->input('content'),
                'status' => $request->input('status'),
                'deleted' => $request->input('deleted')
            ], 422);
            if (!$status)
            {
                return response()->json([
                    'status' => 'error creating',
                    'message' => 'design has been not created',
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'design has been created successfully'
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'status' => 'error catching exception',
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $current_design = Design::find($id);
            if (!$current_design)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Design not found',
                ], 404);
            }

            $rules = [
                'name' => 'required|string|max:256',
                'deleted' => 'required|boolean',
                'content' => 'nullable|string',
                'status' => 'nullable|string',
            ];

            $inputs = $request->only('name', 'content', 'status', 'deleted');
            $validation_errors = Validator::make($inputs, $rules);

            if ($validation_errors->fails())
            {
                return response()->json([
                    'status' => 'error validation',
                    'message' => $validation_errors->errors()->all()
                ], 403);
            }
            $status = $current_design->fill($inputs)->save();
            if (!$status)
            {
                return response()->json([
                    'status' => 'error updating design',
                    'message' => 'Design has not been updated',
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Design has been updated successfully',
                'data' => $inputs
            ], 200);

        }
        catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching exception',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    public function destroy($id)
    {
        $current_design = Design::find($id);
        if (!$current_design)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'design not found',
            ], 404);
        }
        $status = $current_design->delete();
        if (!$status)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'design has not been deleted',
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'design has been deleted',
        ], 200);
    }

}
