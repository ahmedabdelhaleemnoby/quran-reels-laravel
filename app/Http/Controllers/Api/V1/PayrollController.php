<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use OpenApi\Attributes as OA;

class PayrollController extends Controller
{
  #[OA\Get(
    path: "/api/v1/payroll/periods",
    summary: "Get all payroll periods",
    tags: ["Payroll"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Payroll periods list")
    ]
  )]
  public function periods(Request $request)
  {
    $periods = PayrollPeriod::orderBy('start_date', 'desc')->paginate(12);

    return response()->json([
      'success' => true,
      'data' => $periods->items(),
      'meta' => [
        'total' => $periods->total(),
        'per_page' => $periods->perPage(),
        'current_page' => $periods->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/payroll/periods",
    summary: "Create a new payroll period",
    tags: ["Payroll"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Payroll period created")
    ]
  )]
  public function createPeriod(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after:start_date',
      'working_days' => 'required|integer|min:1',
    ]);

    $period = PayrollPeriod::create($validated);

    return response()->json([
      'success' => true,
      'data' => $period,
      'message' => 'Payroll period created successfully'
    ]);
  }

  #[OA\Get(
    path: "/api/v1/payroll/records",
    summary: "Get payroll records",
    tags: ["Payroll"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Payroll records list")
    ]
  )]
  public function records(Request $request)
  {
    $query = PayrollRecord::with(['employee', 'payrollPeriod']);

    if ($request->has('period_id')) {
      $query->where('payroll_period_id', $request->period_id);
    }

    $user = $request->user();

    // Check permissions
    if (!$user->hasRole('admin') && !$user->hasRole('finance_manager')) {
      // Essential: Employee must be linked
      if (!$user->employee_id && !$user->employee) {
        return response()->json(['success' => false, 'message' => 'User not linked to employee'], 403);
      }
      $employeeId = $user->employee_id ?? $user->employee->id;
      $query->where('employee_id', $employeeId);
    } elseif ($request->has('employee_id')) {
      $query->where('employee_id', $request->employee_id);
    }

    $records = $query->orderBy('created_at', 'desc')->paginate(20);

    return response()->json([
      'success' => true,
      'data' => $records->items(),
      'meta' => [
        'total' => $records->total(),
        'per_page' => $records->perPage(),
        'current_page' => $records->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/payroll/calculate",
    summary: "Calculate payroll for a period",
    tags: ["Payroll"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Payroll calculated")
    ]
  )]
  public function calculate(Request $request)
  {
    $validated = $request->validate([
      'period_id' => 'required|exists:payroll_periods,id',
    ]);

    $period = PayrollPeriod::findOrFail($validated['period_id']);

    // Get all active employees with salary structures
    $employees = Employee::where('employment_status', 'active')->get();
    $recordsCreated = 0;
    $totalGross = 0;
    $totalDeductions = 0;
    $totalNet = 0;

    foreach ($employees as $employee) {
      // Check if record already exists
      $exists = PayrollRecord::where('employee_id', $employee->id)
        ->where('payroll_period_id', $period->id)
        ->exists();

      if ($exists)
        continue;

      // Get salary structure or use default from employee
      $salary = SalaryStructure::where('employee_id', $employee->id)
        ->where('active', true)
        ->first();

      $basicSalary = $salary ? $salary->basic_salary : ($employee->salary ?? 5000);
      $allowances = $salary ? $salary->total_allowances : 0;
      $taxRate = $salary ? $salary->tax_rate / 100 : 0.1;
      $insurance = $salary ? $salary->social_insurance : 0;

      $grossSalary = $basicSalary + $allowances;
      $taxDeduction = $grossSalary * $taxRate;
      $totalDeduction = $taxDeduction + $insurance;
      $netSalary = $grossSalary - $totalDeduction;

      PayrollRecord::create([
        'employee_id' => $employee->id,
        'payroll_period_id' => $period->id,
        'basic_salary' => $basicSalary,
        'allowances' => $allowances,
        'bonuses' => 0,
        'overtime_pay' => 0,
        'gross_salary' => $grossSalary,
        'tax_deduction' => $taxDeduction,
        'insurance_deduction' => $insurance,
        'other_deductions' => 0,
        'total_deductions' => $totalDeduction,
        'net_salary' => $netSalary,
        'days_worked' => $period->working_days,
        'days_absent' => 0,
        'status' => 'draft'
      ]);

      $totalGross += $grossSalary;
      $totalDeductions += $totalDeduction;
      $totalNet += $netSalary;
      $recordsCreated++;
    }

    // Update period totals
    $period->update([
      'total_gross' => $totalGross,
      'total_deductions' => $totalDeductions,
      'total_net' => $totalNet,
      'status' => 'processing'
    ]);

    return response()->json([
      'success' => true,
      'data' => [
        'records_created' => $recordsCreated,
        'total_gross' => $totalGross,
        'total_deductions' => $totalDeductions,
        'total_net' => $totalNet
      ],
      'message' => "Payroll calculated for {$recordsCreated} employees"
    ]);
  }

  #[OA\Get(
    path: "/api/v1/payroll/my-payslips",
    summary: "Get current user payslips",
    tags: ["Payroll"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "User payslips")
    ]
  )]
  public function myPayslips(Request $request)
  {
    $user = $request->user();
    if (!$user->employee_id && !$user->employee) {
      return response()->json(['success' => false, 'message' => 'User not linked to employee'], 400);
    }

    $employeeId = $user->employee->id ?? $user->employee_id;

    $payslips = PayrollRecord::with('payrollPeriod')
      ->where('employee_id', $employeeId)
      ->orderBy('created_at', 'desc')
      ->get();

    return response()->json([
      'success' => true,
      'data' => $payslips
    ]);
  }

  #[OA\Put(
    path: "/api/v1/payroll/periods/{id}/approve",
    summary: "Approve a payroll period",
    tags: ["Payroll"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Payroll period approved")
    ]
  )]
  public function approvePeriod(Request $request, $id)
  {
    $period = PayrollPeriod::findOrFail($id);

    $period->update([
      'status' => 'approved',
      'approved_by' => $request->user()->id,
      'approved_at' => Carbon::now()
    ]);

    // Also approve all records
    PayrollRecord::where('payroll_period_id', $id)->update(['status' => 'approved']);

    return response()->json([
      'success' => true,
      'data' => $period,
      'message' => 'Payroll period approved successfully'
    ]);
  }

  #[OA\Get(
    path: "/api/v1/payroll/records/{id}/pdf",
    summary: "Download a payslip as PDF",
    tags: ["Payroll"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Payslip PDF"),
      new OA\Response(response: 403, description: "Unauthorized"),
      new OA\Response(response: 404, description: "Payslip not found")
    ]
  )]
  public function downloadPdf(Request $request, $id)
  {
    $record = PayrollRecord::with(['employee', 'payrollPeriod'])->findOrFail($id);
    $user = $request->user();

    // Permission check: Admin, Finance, or the Employee themselves
    if (!$user->hasRole('admin') && !$user->hasRole('finance_manager')) {
      $employeeId = $user->employee_id ?? ($user->employee ? $user->employee->id : null);
      if ($record->employee_id != $employeeId) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
      }
    }

    $locale = $request->get('locale', 'en');

    $pdf = Pdf::loadView('pdfs.payslip', [
      'record' => $record,
      'locale' => $locale
    ]);

    $filename = "payslip_{$record->employee->employee_code}_{$record->payrollPeriod->name}.pdf";
    return $pdf->download($filename);
  }
}
