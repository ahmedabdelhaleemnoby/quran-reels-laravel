<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
  /**
   * Perform a global search across different entities.
   */
  public function search(Request $request)
  {
    $query = $request->get('q');

    if (empty($query) || strlen($query) < 2) {
      return response()->json([
        'success' => true,
        'data' => [
          'employees' => [],
          'departments' => []
        ]
      ]);
    }

    // Search Employees
    $employees = Employee::where(function ($q) use ($query) {
      $q->where('first_name', 'like', "%{$query}%")
        ->orWhere('last_name', 'like', "%{$query}%")
        ->orWhere('email', 'like', "%{$query}%")
        ->orWhere('employee_code', 'like', "%{$query}%")
        ->orWhere('position', 'like', "%{$query}%");
    })
      ->take(10)
      ->get(['id', 'first_name', 'last_name', 'email', 'position', 'department', 'employee_code']);

    // Search Departments
    $departments = Employee::where('department', 'like', "%{$query}%")
      ->distinct()
      ->take(5)
      ->pluck('department')
      ->map(function ($dept) {
        return [
          'name' => $dept,
          'type' => 'department'
        ];
      });

    return response()->json([
      'success' => true,
      'data' => [
        'employees' => $employees,
        'departments' => $departments
      ]
    ]);
  }
}
