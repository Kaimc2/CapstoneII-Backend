<?php

namespace App\Http\Controllers;

use App\Enums\Pagination;
use App\Models\Commission;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);

            $commissions = DB::table('commissions')
                ->join('designs', 'designs.id', '=', 'commissions.design_id')
                ->join('users', 'users.id', '=', 'designs.user_id')
                ->join('stores', 'stores.id', '=', 'commissions.tailor_id')
                ->latest('commissions.updated_at')
                ->where('designs.name', 'LIKE', "%{$search}%")
                ->select(
                    'commissions.id',
                    'commissions.design_id as designId',
                    'designs.name as designName',
                    'users.id as designOwnerId',
                    'users.name as designOwnerName',
                    'commissions.tailor_id as tailorId',
                    'stores.name as tailorName',
                    'commissions.options',
                    'commissions.total',
                    'commissions.type',
                    'commissions.status',
                    'commissions.start_date as startDate',
                    'commissions.end_date as endDate',
                )
                ->paginate($item_per_page);

            return response()->json([
                'status' => 'Success',
                'data' => $commissions
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function my_commissions(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $user = auth()->user();

            $commissions = DB::table('commissions')
                ->join('designs', 'designs.id', '=', 'commissions.design_id')
                ->join('users', 'users.id', '=', 'designs.user_id')
                ->join('stores', 'stores.id', '=', 'commissions.tailor_id')
                ->latest('commissions.updated_at')
                ->where('designs.name', 'LIKE', "%{$search}%")
                ->where('designs.user_id', $user->id)
                ->select(
                    'commissions.id',
                    'commissions.design_id as designId',
                    'designs.name as designName',
                    'users.id as designOwnerId',
                    'users.name as designOwnerName',
                    'commissions.tailor_id as tailorId',
                    'stores.name as tailorName',
                    'commissions.options',
                    'commissions.total',
                    'commissions.type',
                    'commissions.status',
                    'commissions.start_date as startDate',
                    'commissions.end_date as endDate',
                )
                ->paginate($item_per_page);

            return response()->json([
                'status' => 'Success',
                'data' => $commissions
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function store_commissions(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $user = auth()->user();

            $commissions = DB::table('commissions')
                ->join('designs', 'designs.id', '=', 'commissions.design_id')
                ->join('users', 'users.id', '=', 'designs.user_id')
                ->join('stores', 'stores.id', '=', 'commissions.tailor_id')
                ->latest('commissions.updated_at')
                ->where('designs.name', 'LIKE', "%{$search}%")
                ->where('stores.owner_id', $user->id)
                ->select(
                    'commissions.id',
                    'commissions.design_id as designId',
                    'designs.name as designName',
                    'users.id as designOwnerId',
                    'users.name as designOwnerName',
                    'commissions.tailor_id as tailorId',
                    'stores.name as tailorName',
                    'commissions.options',
                    'commissions.total',
                    'commissions.type',
                    'commissions.status',
                    'commissions.start_date as startDate',
                    'commissions.end_date as endDate',
                )
                ->paginate($item_per_page);

            return response()->json([
                'status' => 'Success',
                'data' => $commissions
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function show_recent()
    {
        try {
            $user = auth()->user();

            $commissions = DB::table('commissions')
                ->join('designs', 'designs.id', '=', 'commissions.design_id')
                ->join('users', 'users.id', '=', 'designs.user_id')
                ->join('stores', 'stores.id', '=', 'commissions.tailor_id')
                ->latest('commissions.updated_at')
                ->where('designs.user_id', $user->id)
                ->select(
                    'commissions.id',
                    'commissions.design_id as designId',
                    'designs.name as designName',
                    'users.id as designOwnerId',
                    'users.name as designOwnerName',
                    'commissions.tailor_id as tailorId',
                    'stores.name as tailorName',
                    'commissions.options',
                    'commissions.total',
                    'commissions.type',
                    'commissions.status',
                    'commissions.start_date as startDate',
                    'commissions.end_date as endDate',
                )
                ->latest('commissions.updated_at')
                ->take(5)->get();

            return response()->json([
                'status' => 'Success',
                'data' => $commissions
            ], 200);
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
            $commission =  DB::table('commissions')
                ->where('commissions.id', $id)
                ->latest('commissions.updated_at')
                ->select(
                    'commissions.id',
                    'commissions.design_id as designId',
                    'commissions.tailor_id as tailorId',
                    'commissions.options',
                    'commissions.total',
                    'commissions.type',
                    'commissions.status',
                    'commissions.start_date as startDate',
                    'commissions.end_date as endDate',
                )
                ->first();

            $store = Store::find($commission->tailorId);
            $store->materials = DB::table('store_materials')
                ->join('materials', 'materials.id', '=', 'store_materials.material_id')
                ->where('store_materials.store_id', $store->id)
                ->select(
                    'store_materials.id',
                    'materials.id as materialID',
                    'store_materials.store_id as storeID',
                    'materials.name',
                    'store_materials.price'
                )->get();
            $store->colors = DB::table('store_colors')
                ->join('colors', 'colors.id', '=', 'store_colors.color_id')
                ->where('store_colors.store_id', $store->id)
                ->select(
                    'store_colors.id',
                    'colors.id as colorID',
                    'store_colors.store_id as storeID',
                    'colors.name',
                    'colors.hex_code as hexCode',
                    'store_colors.price'
                )->get();
            $store->sizes = DB::table('store_sizes')
                ->join('sizes', 'sizes.id', '=', 'store_sizes.size_id')
                ->where('store_sizes.store_id', $store->id)
                ->select(
                    'store_sizes.id',
                    'sizes.id as sizeID',
                    'store_sizes.store_id as storeID',
                    'sizes.name',
                    'store_sizes.price'
                )->get();

            $design = DB::table('designs')
                ->join('commissions', 'commissions.design_id', '=', 'designs.id')
                ->where('commissions.id', $id)
                ->first();

            $commission->store = $store;
            $commission->design = $design;

            if (!$commission) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Commission not found!'
                ], 404);
            }

            return response()->json([
                'status' => 'Success',
                'data' => $commission
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
                'design_id' => 'required|string',
                'tailor_id' => 'required|string',
                'options' => 'required|string',
                'total' => 'required|numeric',
                'status' => 'required|numeric',
                'type' => 'required|string',
                'start_date' => 'date',
                'end_date' => 'date',
            ];

            $inputs = $request->only('design_id', 'tailor_id', 'options', 'total', 'type', 'status', 'start_date', 'end_date');
            $validation = Validator::make($inputs, $rules);

            if ($validation->fails()) {
                return response()->json([
                    'status' => 'Error',
                    'message' => $validation->errors()->all()
                ], 422);
            }

            $design = Commission::create([...$inputs, "start_date" => Carbon::now()]);

            return response()->json([
                'status' => 'Success',
                'message' => 'Commission created successfully',
                'data' => $design
            ], 201);
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
            $commission = Commission::find($id);

            if (!$commission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Commission not found'
                ], 404);
            }

            $rules = [
                'design_id' => 'string',
                'tailor_id' => 'string',
                'options' => 'string',
                'total' => 'numeric',
                'type' => 'string',
                'status' => 'numeric',
                'start_date' => 'date',
                'end_date' => 'date',
            ];

            $inputs = $request->only('design_id', 'tailor_id', 'options', 'total', 'type', 'status', 'start_date', 'end_date');
            $validator = Validator::make($inputs, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $commission->update($inputs);

            return response()->json([
                'status' => 'success',
                'message' => 'Commission has been updated successfully',
                'data' => $commission
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
            $commission = Commission::find($id);

            if (!$commission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Commission not found'
                ], 404);
            }

            $commission->status = 3;
            $commission->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Commission deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
