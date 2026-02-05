<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
  public function run(): void
  {
    $types = [
      [
        'name' => 'Annual Leave',
        'code' => 'AL',
        'description' => 'Standard annual paid leave',
        'is_paid' => true,
        'max_days_per_year' => 21,
        'color' => '#4caf50',
      ],
      [
        'name' => 'Sick Leave',
        'code' => 'SL',
        'description' => 'Medical leave with doctor certificate',
        'is_paid' => true,
        'max_days_per_year' => 15,
        'color' => '#f44336',
      ],
      [
        'name' => 'Unpaid Leave',
        'code' => 'UL',
        'description' => 'Leave without pay',
        'is_paid' => false,
        'max_days_per_year' => null,
        'color' => '#9e9e9e',
      ],
      [
        'name' => 'Maternity Leave',
        'code' => 'ML',
        'description' => 'Paid leave for new mothers',
        'is_paid' => true,
        'max_days_per_year' => 90,
        'color' => '#e91e63',
      ],
      [
        'name' => 'Compassionate Leave',
        'code' => 'CL',
        'description' => 'Leave for family emergencies or bereavement',
        'is_paid' => true,
        'max_days_per_year' => 5,
        'color' => '#2196f3',
      ],
    ];

    foreach ($types as $type) {
      LeaveType::updateOrCreate(['code' => $type['code']], $type);
    }
  }
}
