<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class EmployeeController extends Controller
{
  #[OA\Get(
    path: "/api/v1/employees",
    summary: "Get list of all employees",
    tags: ["Employees"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(
        response: 200,
        description: "List of employees",
        content: new OA\JsonContent(
          properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
          ]
        )
      ),
      new OA\Response(response: 401, description: "Unauthenticated")
    ]
  )]
  public function index(Request $request)
  {
    $this->authorize('viewAny', Employee::class);

    $user = $request->user();
    $query = Employee::query();

    // RBAC Logic: Filter employees based on current user's role/permissions
    if (!$user->hasRole('admin') && !$user->hasPermissionTo('manage_employees')) {
      if ($user->hasPermissionTo('view_dept_salaries')) {
        $query->where('department', $user->employee->department ?? null);
      } elseif ($user->hasPermissionTo('view_own_salary')) {
        $query->where('id', $user->employee_id);
      }
    }

    if ($request->has('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%")
          ->orWhere('employee_code', 'like', "%{$search}%");
      });
    }

    $perPage = $request->get('per_page', 10);
    $employees = $query->paginate($perPage);

    return response()->json([
      'success' => true,
      'data' => $employees->items(),
      'meta' => [
        'current_page' => $employees->currentPage(),
        'last_page' => $employees->lastPage(),
        'per_page' => $employees->perPage(),
        'total' => $employees->total(),
      ]
    ]);
  }

  #[OA\Get(
    path: "/api/v1/employees/{id}",
    summary: "Get employee details",
    tags: ["Employees"],
    security: [["sanctum" => []]],
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    responses: [
      new OA\Response(response: 200, description: "Employee details"),
      new OA\Response(response: 404, description: "Employee not found")
    ]
  )]
  public function show($id)
  {
    $employee = Employee::with('documents')->find($id);

    if (!$employee) {
      return response()->json([
        'success' => false,
        'message' => 'Employee not found'
      ], 404);
    }

    // Append full URL to avatar if it exists
    if ($employee->avatar_url) {
      $employee->avatar_url = url('storage/' . $employee->avatar_url);
    }

    return response()->json([
      'success' => true,
      'data' => $employee
    ]);
  }

  public function store(Request $request)
  {
    $this->authorize('create', Employee::class);

    $validated = $request->validate([
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'email' => 'required|email|unique:employees,email',
      'phone' => 'nullable|string|max:20',
      'department' => 'nullable|string|max:255',
      'position' => 'nullable|string|max:255',
      'employment_status' => 'required|in:active,on_leave,terminated',
      'employment_type' => 'required|in:full_time,part_time,contract',
      'hire_date' => 'required|date',
      'salary' => 'nullable|numeric',
      'address' => 'nullable|string',
      'contract_end_date' => 'nullable|date',
      'probation_end_date' => 'nullable|date',
    ]);

    // Generate a unique employee code
    if (!$request->has('employee_code')) {
      $lastEmployee = Employee::orderBy('id', 'desc')->first();
      $nextId = $lastEmployee ? $lastEmployee->id + 1 : 1;
      $validated['employee_code'] = 'EMP' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

      // Final safety check to ensure uniqueness
      while (Employee::where('employee_code', $validated['employee_code'])->exists()) {
        $nextId++;
        $validated['employee_code'] = 'EMP' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
      }
    } else {
      $validated['employee_code'] = $request->employee_code;
    }

    $employee = Employee::create($validated);

    return response()->json([
      'success' => true,
      'data' => $employee,
      'message' => 'Employee created successfully'
    ], 201);
  }

  public function update(Request $request, $id)
  {
    $employee = Employee::findOrFail($id);

    $validated = $request->validate([
      'first_name' => 'sometimes|string|max:255',
      'last_name' => 'sometimes|string|max:255',
      'email' => 'sometimes|email|unique:employees,email,' . $id,
      'phone' => 'nullable|string|max:20',
      'department' => 'nullable|string|max:255',
      'position' => 'nullable|string|max:255',
      'employment_status' => 'sometimes|in:active,on_leave,terminated',
      'employment_type' => 'sometimes|in:full_time,part_time,contract',
      'hire_date' => 'nullable|date',
      'salary' => 'nullable|numeric',
      'address' => 'nullable|string',
      'contract_end_date' => 'nullable|date',
      'probation_end_date' => 'nullable|date',
    ]);

    $employee->update($validated);

    return response()->json([
      'success' => true,
      'data' => $employee
    ]);
  }

  public function uploadAvatar(Request $request, $id)
  {
    $request->validate([
      'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $employee = Employee::findOrFail($id);

    if ($request->hasFile('avatar')) {
      // Delete old avatar if exists
      if ($employee->avatar_url) {
        Storage::disk('public')->delete($employee->avatar_url);
      }

      $file = $request->file('avatar');
      $path = $file->store('avatars', 'public');

      $employee->update([
        'avatar_url' => $path
      ]);

      return response()->json([
        'success' => true,
        'avatar_url' => url('storage/' . $path),
        'message' => 'Avatar uploaded successfully'
      ]);
    }

    return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
  }

  public function uploadDocument(Request $request, $id)
  {
    $request->validate([
      'document' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:10240',
      'name' => 'required|string|max:255',
    ]);

    $employee = Employee::findOrFail($id);

    if ($request->hasFile('document')) {
      $file = $request->file('document');
      $path = $file->store('documents/' . $id, 'public');

      $document = EmployeeDocument::create([
        'employee_id' => $id,
        'name' => $request->name,
        'file_path' => $path,
        'file_type' => $file->getClientOriginalExtension(),
        'file_size' => $file->getSize(),
      ]);

      return response()->json([
        'success' => true,
        'data' => $document,
        'message' => 'Document uploaded successfully'
      ]);
    }

    return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
  }

  public function deleteDocument($id)
  {
    $document = EmployeeDocument::findOrFail($id);

    // Delete file from storage
    Storage::disk('public')->delete($document->file_path);

    // Delete from database
    $document->delete();

    return response()->json([
      'success' => true,
      'message' => 'Document deleted successfully'
    ]);
  }

  public function destroy($id)
  {
    $employee = Employee::findOrFail($id);

    $this->authorize('delete', $employee);

    // Delete avatar if exists
    if ($employee->avatar_url) {
      Storage::disk('public')->delete($employee->avatar_url);
    }

    // Delete all documents from storage
    foreach ($employee->documents as $document) {
      Storage::disk('public')->delete($document->file_path);
    }

    // Related database records are handled by DB-level cascade delete
    // (attendances, leave_requests, performance_reviews, payroll_records, okrs, goals, employee_documents)

    $employee->delete();

    return response()->json([
      'success' => true,
      'message' => 'Employee and all related data deleted successfully'
    ]);
  }
}
