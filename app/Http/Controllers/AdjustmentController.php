<?php

namespace App\Http\Controllers;

use App\Enums\Pagination;
use App\Http\Controllers\Controller;
use App\Models\Adjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdjustmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);

            $adjustments = DB::table('adjustments')
                ->join('commissions', 'commissions.id', '=', 'adjustments.commission_id')
                ->join('stores', 'stores.id', '=', 'commissions.tailor_id')
                // ->where('adjustments.message', 'LIKE', "%{$search}%")
                ->where('stores.name', 'LIKE', "%{$search}%")
                ->select([
                    'adjustments.id',
                    'adjustments.commission_id as commissionId',
                    'adjustments.adjustment_date as adjustmentDate',
                    'adjustments.duration',
                    'adjustments.message',
                    'stores.name as tailorName',
                ])
                ->paginate($item_per_page);

            return response()->json([
                'status' => 'success',
                'data' => $adjustments,
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
                'adjustment_date' => 'required|date',
                'duration' => 'nullable|string',
                'message' => 'nullable|string'
            ];

            $inputs = $request->only('commission_id', 'adjustment_date', 'duration', 'message');
            $validator = Validator::make($inputs, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $design = Adjustment::create($inputs);

            //update complete_date in commissions
            $updated = DB::table('commissions')
                ->where('id', $design->commission_id)
                ->update([
                    'end_date' => DB::raw("DATE_ADD(end_date, INTERVAL {$design->duration} DAY)")
                ]);

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
