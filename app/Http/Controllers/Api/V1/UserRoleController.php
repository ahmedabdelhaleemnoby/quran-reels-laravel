<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
  public function index(Request $request)
  {
    $query = User::with(['roles', 'employee']);

    if ($request->has('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      });
    }

    $perPage = $request->get('per_page', 10);
    $users = $query->paginate($perPage);

    return response()->json([
      'success' => true,
      'data' => $users->items(),
      'meta' => [
        'current_page' => $users->currentPage(),
        'last_page' => $users->lastPage(),
        'per_page' => $users->perPage(),
        'total' => $users->total(),
      ]
    ]);
  }

  public function update(Request $request, $userId)
  {
    $user = User::findOrFail($userId);

    $validated = $request->validate([
      'roles' => 'required|array',
      'roles.*' => 'exists:roles,name'
    ]);

    $user->syncRoles($validated['roles']);

    return response()->json([
      'success' => true,
      'data' => $user->load('roles'),
      'message' => 'User roles updated successfully'
    ]);
  }

  public function show($userId)
  {
    $user = User::with(['roles', 'employee'])->findOrFail($userId);

    return response()->json([
      'success' => true,
      'data' => $user
    ]);
  }
}
