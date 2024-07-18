<?php

namespace App\Http\Controllers;

use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Models\StoreColor;
use App\Models\StoreMaterial;
use App\Models\StoreSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', 10);

            $stores = Store::query();

            $data = $stores->whereAny(['name', 'description'], 'LIKE', "%{$search}%")
                ->orderBy('name', 'asc')
                ->paginate($item_per_page);

            return response()->json([
                'status' => 'success',
                'data' => StoreResource::collection($data),
                'meta' => [
                    'currentPage' => $data->currentPage(),
                    'from' => $data->firstItem(),
                    'lastPage' => $data->lastPage(),
                    'perPage' => $data->perPage(),
                    'to' => $data->lastItem(),
                    'total' => $data->total(),
                ],
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching exception',
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function show($id)
    {
        try {
            $data = Store::where('owner_id', '=', $id)->first();

            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Store not found'
                ]);
            }

            $data->materials = StoreMaterial::where('store_id', '=', $data->id)->get();
            $data->colors = StoreColor::where('store_id', '=', $data->id)->get();
            $data->sizes = StoreSize::where('store_id', '=', $data->id)->get();

            return response()->json([
                'status' => 'success',
                'data' => new StoreResource($data)
            ]);
        } catch (\Exception $ex) {
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
                'tailor_thumbnail' => 'required|string',
                'address' => 'string',
                'phone_number' => 'required|string',
                'email' => 'required|email',
                'owner_id' => 'required|numeric',
            ];
            $inputs = $request->only('name', 'description', 'tailor_thumbnail', 'address', 'phone_number', 'email', 'owner_id');
            $validation_errors = Validator::make($inputs, $rules);

            if ($validation_errors->fails()) {
                return response()->json([
                    'status' => 'error validation',
                    'message' => $validation_errors->errors()->all()
                ], 403);
            }

            $status_creation = Store::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'tailor_thumbnail' => $request->input('tailor_thumbnail'),
                'address' => $request->input('address'),
                'phone_number' => $request->input('phone_number'),
                'email' => $request->input('email'),
                'owner_id' => $request->input('owner_id'),
            ]);

            if (!$status_creation) {
                return response()->json([
                    'status' => 'error creating',
                    'message' => 'Store has not been created'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Store has been created successfully'
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching exception',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $store = Store::find($id);

            if (!$store) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Store not found',
                ], 404);
            }

            $rules = [
                'name' => 'required|string',
                'description' => 'required|string',
                'tailor_thumbnail' => 'required|string',
                'address' => 'string',
                'phone_number' => 'required|string',
                'email' => 'required|email',
                'owner_id' => 'required|numeric',
            ];
            $inputs = $request->only('name', 'description', 'tailor_thumbnail', 'address', 'phone_number', 'email', 'owner_id');
            $validation_errors = Validator::make($inputs, $rules);

            if ($validation_errors->fails()) {
                return response()->json([
                    'status' => 'error validation',
                    'message' => $validation_errors->errors()->all()
                ], 403);
            }

            $status_update = $store->fill($inputs)->save();

            if (!$status_update) {
                return response()->json([
                    'status' => 'error updating',
                    'message' => 'Store has not been updated'
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Store has been updated successfully'
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching exception',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    public function destroy($id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json([
                'status' => 'error',
                'message' => 'Store not found',
            ], 404);
        }

        $status_delete = $store->delete();

        if (!$status_delete) {
            return response()->json([
                'status' => 'error deleting',
                'message' => 'Store has not been deleted',
            ]);
        }

        return response()->json([
            'status' => 'success deleting',
            'message' => 'Store has been deleted successfully',
        ]);
    }
}
