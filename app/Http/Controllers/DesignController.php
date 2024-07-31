<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enums\Pagination;
use App\Http\Resources\DesignResource;
use Illuminate\Support\Facades\Storage;

class DesignController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $designs = Design::query();
            $user = auth()->user();

            if ($search) {
                $designs->whereAny(['name'], 'LIKE', "%{$search}%")
                    ->where('user_id', '=', $user->id)
                    ->where('deleted', '=', false)
                    ->orderBy('id', 'ASC')
                    ->paginate($item_per_page);
            }

            $data = $designs
                ->where('user_id', '=', $user->id)
                ->where('deleted', '=', false)
                ->orderBy('name', 'asc')->paginate($item_per_page);

            return response()->json([
                'status' => 'success',
                'data' => DesignResource::collection($data),
                'meta' => [
                    'currentPage' => $data->currentPage(),
                    'from' => $data->firstItem(),
                    'lastPage' => $data->lastPage(),
                    'perPage' => $data->perPage(),
                    'to' => $data->lastItem(),
                    'total' => $data->total(),
                ],
                'message' => 'Data has been retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function show_recent()
    {
        try {
            $designs = Design::query();
            $user = auth()->user();

            $data = $designs
                ->where('user_id', '=', $user->id)
                ->where('deleted', '=', false)
                ->latest('updated_at')
                ->take(5)->get();

            return response()->json([
                'status' => 'success',
                'data' => DesignResource::collection($data),
                'message' => 'Data has been retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function show_deleted(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', Pagination::ITEMS_PER_PAGE->value);
            $designs = Design::query();
            $user = auth()->user();

            if ($search) {
                $designs->whereAny(['name'], 'LIKE', "%{$search}%")
                    ->where('user_id', '=', $user->id)
                    ->where('deleted', '=', true)
                    ->orderBy('id', 'ASC')
                    ->paginate($item_per_page);
            }

            $data = $designs
                ->where('user_id', '=', $user->id)
                ->where('deleted', '=', true)
                ->orderBy('name', 'asc')->paginate($item_per_page);

            return response()->json([
                'status' => 'success',
                'data' => DesignResource::collection($data),
                'meta' => [
                    'currentPage' => $data->currentPage(),
                    'from' => $data->firstItem(),
                    'lastPage' => $data->lastPage(),
                    'perPage' => $data->perPage(),
                    'to' => $data->lastItem(),
                    'total' => $data->total(),
                ],
                'message' => 'Data has been retrieved successfully'
            ]);
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
            $user = auth()->user();
            $design = Design::find($id);

            if (!$design || $design->deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Design not found'
                ], 404);
            }

            if ($user->id !== $design->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
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

    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:256',
                'user_id' => 'required|string',
                'deleted' => 'nullable|boolean',
                'design_thumbnail' => 'required|string',
                'front_content' => 'required|string',
                'back_content' => 'required|string',
                'status' => 'nullable|string'
            ];

            $inputs = $request->only('name', 'user_id', 'deleted', 'design_thumbnail', 'front_content', 'back_content', 'status');
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
                'user_id' => 'required|string',
                'deleted' => 'nullable|boolean',
                'design_thumbnail' => 'required|string',
                'front_content' => 'required|string',
                'back_content' => 'required|string',
                'status' => 'nullable|string'
            ];

            $inputs = $request->only('name', 'user_id', 'deleted', 'design_thumbnail', 'front_content', 'back_content', 'status');
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

    public function restore($id)
    {
        try {
            $design = Design::find($id);

            if (!$design) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Design not found'
                ], 404);
            }

            $design->deleted = false;
            $design->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Design restored successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
