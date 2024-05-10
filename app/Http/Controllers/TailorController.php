<?php

namespace App\Http\Controllers;
use App\Models\Tailor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TailorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', 10);

            $tailors = Tailor::query();

            $data = $tailors->whereAny(['name', 'description', 'price'], 'LIKE', $search)
                ->orderBy('name', 'asc')
                ->paginate($item_per_page);
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        }
        catch (\Exception $ex)
        {
            return response()->json([
                'status' => 'error catching exception',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'description' => 'required|string',
                'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            ];
            $inputs = $request->only('name', 'description', 'price');
            $validation_errors = Validator::make($inputs, $rules);
            if ($validation_errors->fails())
            {
                return response()->json([
                    'status' => 'error validation',
                    'message' => $validation_errors->errors()->all()
                ], 403);
            }
            $status_creation = Tailor::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
            ]);
            if(!$status_creation)
            {
                return response()->json([
                    'status' => 'error creating',
                    'message' => 'tailor has not been created'
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'tailor has been created successfully'
            ]);
        }
        catch (\Exception $ex)
        {
            return response()->json([
                'status' => 'error catching exception',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $current_tailor = Tailor::find($id);
            if (!$current_tailor)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'tailor not found',
                ], 404);
            }
            $rules = [
                'name' => 'required|string',
                'description' => 'required|string',
                'price' => 'required|numeric',
            ];
            $inputs = $request->only('name', 'description', 'price');
            $validation_errors = Validator::make($inputs, $rules);
            if ($validation_errors->fails())
            {
                return response()->json([
                    'status' => 'error validation',
                    'message' => $validation_errors->errors()->all()
                ], 403);
            }
            $status_update = $current_tailor->fill($inputs)->save();
            if (!$status_update)
            {
                return response()->json([
                    'status' => 'error updating',
                    'message' => 'tailor has not been updated'
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'tailor has been updated successfully'
            ]);
        }
        catch (\Exception $ex)
        {
            return response()->json([
                'status' => 'error catching exception',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    public function destroy($id)
    {
        $tailor = Tailor::find($id);
        if (!$tailor)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'tailor not found',
            ], 404);
        }
        $status_delete = $tailor->delete();
        if (!$status_delete)
        {
            return response()->json([
                'status' => 'error deleting',
                'message' => 'tailor has not been deleted',
            ]);
        }
        return response()->json([
            'status' => 'success deleting',
            'message' => 'tailor has been deleted successfully',
        ]);
    }
}
