<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use function Symfony\Component\Translation\t;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $item_per_page = $request->input('item_per_page', 5);
            $users = User::with('getRole')->with('getPermission')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', $search)
                        ->whereHas('getRole', function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%$search%");
                        });
                })->paginate($item_per_page);
            //            $roles = Role::findById(1)->getPermissionNames();
            //            $permissions = ['permission-list', 'permission-create', 'permission-show', 'permission-delete', 'permission-edit'];
            //            $roles->syncPermissions($permissions);
            return response()->json([
                'status' => 'success',
                'data' => $users,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error catching',
                'message' => $ex->getMessage()
            ]);
        }
    }
}
