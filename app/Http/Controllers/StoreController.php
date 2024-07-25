<?php

namespace App\Http\Controllers;

use App\Http\Resources\StoreResource;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function show($id, Request $request)
    {
        try {
            $data = Store::where('owner_id', '=', $id)->first();

            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Store not found'
                ]);
            }

            $data->materials = DB::table('store_materials')
                ->join('materials', 'materials.id', '=', 'store_materials.material_id')
                ->where('store_materials.store_id', $data->id)
                ->where('materials.name', 'LIKE', "%{$request->search_material}")
                ->select(
                    'store_materials.id',
                    'materials.id as materialID',
                    'store_materials.store_id as storeID',
                    'materials.name',
                    'store_materials.price'
                )->get();
            $data->colors = DB::table('store_colors')
                ->join('colors', 'colors.id', '=', 'store_colors.color_id')
                ->where('store_colors.store_id', $data->id)
                ->where('colors.name', 'LIKE', "%{$request->search_color}")
                ->select(
                    'store_colors.id',
                    'colors.id as colorID',
                    'store_colors.store_id as storeID',
                    'colors.name',
                    'colors.hex_code as hexCode',
                    'store_colors.price'
                )->get();
            $data->sizes = DB::table('store_sizes')
                ->join('sizes', 'sizes.id', '=', 'store_sizes.size_id')
                ->where('store_sizes.store_id', $data->id)
                ->where('sizes.name', 'LIKE', "%{$request->search_size}")
                ->select(
                    'store_sizes.id',
                    'sizes.id as sizeID',
                    'store_sizes.store_id as storeID',
                    'sizes.name',
                    'store_sizes.price'
                )->get();

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
                'tailor_thumbnail' => 'string',
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
