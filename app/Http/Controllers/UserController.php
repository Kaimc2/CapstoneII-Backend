<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use function Symfony\Component\Translation\t;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validate and sanitize input
            $validated = $request->validate([
                'search' => 'nullable|string|max:255',
                'item_per_page' => 'nullable|integer|min:1|max:100'
            ]);
            $search = $validated['search'] ?? '';
            $item_per_page = $validated['item_per_page'] ?? 5;

            $users = User::where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })->paginate($item_per_page);

            return response()->json([
                'status' => 'success',
                'data' => UserResource::collection($users),
                'meta' => [
                    'currentPage' => $users->currentPage(),
                    'from' => $users->firstItem(),
                    'lastPage' => $users->lastPage(),
                    'perPage' => $users->perPage(),
                    'to' => $users->lastItem(),
                    'total' => $users->total(),
                ],
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching',
                'message' => $ex->getMessage()
            ]);
        }
    }
}
