<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
  public function index()
  {
    $roles = Role::with('permissions')->get();

    return response()->json([
      'success' => true,
      'data' => $roles
    ]);
  }

  public function show($id)
  {
    $role = Role::with('permissions')->findOrFail($id);

    return response()->json([
      'success' => true,
      'data' => $role
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|unique:roles,name',
      'permissions' => 'array',
      'permissions.*' => 'exists:permissions,name'
    ]);

    $role = Role::create(['name' => $validated['name']]);

    if (isset($validated['permissions'])) {
      $role->syncPermissions($validated['permissions']);
    }

    return response()->json([
      'success' => true,
      'data' => $role->load('permissions')
    ], 201);
  }

  public function update(Request $request, $id)
  {
    $role = Role::findOrFail($id);

    $validated = $request->validate([
      'name' => 'sometimes|string|unique:roles,name,' . $id,
      'permissions' => 'array',
      'permissions.*' => 'exists:permissions,name'
    ]);

    if (isset($validated['name'])) {
      $role->update(['name' => $validated['name']]);
    }

    if (isset($validated['permissions'])) {
      $role->syncPermissions($validated['permissions']);
    }

    return response()->json([
      'success' => true,
      'data' => $role->load('permissions')
    ]);
  }

  public function destroy($id)
  {
    $role = Role::findOrFail($id);

    // Prevent deletion of critical roles
    if (in_array($role->name, ['admin', 'dept_manager', 'employee'])) {
      return response()->json([
        'success' => false,
        'message' => 'Cannot delete system roles'
      ], 400);
    }

    $role->delete();

    return response()->json([
      'success' => true,
      'message' => 'Role deleted successfully'
    ]);
  }

  public function permissions()
  {
    $permissions = Permission::all();

    return response()->json([
      'success' => true,
      'data' => $permissions
    ]);
  }
}
