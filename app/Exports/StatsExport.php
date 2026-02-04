<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;

class StatsExport implements WithMultipleSheets
{
  protected $stats;

  public function __construct(array $stats)
  {
    $this->stats = $stats;
  }

  public function sheets(): array
  {
    return [
      new SummarySheet($this->stats),
      new DepartmentSheet($this->stats['department_stats']),
      new RecentActivitySheet($this->stats['recent_activities']),
    ];
  }
}

class SummarySheet implements FromArray, WithHeadings, WithTitle
{
  protected $stats;

  public function __construct($stats)
  {
    $this->stats = $stats;
  }

  public function array(): array
  {
    return [
      [$this->stats['labels']['total_employees'], $this->stats['total_employees']],
      [$this->stats['labels']['present_today'], $this->stats['active_employees']],
      [$this->stats['labels']['on_leave'], $this->stats['on_leave_employees']],
      [$this->stats['labels']['period_employees'], $this->stats['period_employees']],
    ];
  }

  public function headings(): array
  {
    return ['Metric', 'Value'];
  }

  public function title(): string
  {
    return 'Summary';
  }
}

class DepartmentSheet implements FromArray, WithHeadings, WithTitle
{
  protected $departments;

  public function __construct($departments)
  {
    $this->departments = $departments;
  }

  public function array(): array
  {
    return collect($this->departments)->map(function ($dept) {
      return [
        $dept['department'],
        $dept['count'],
      ];
    })->toArray();
  }

  public function headings(): array
  {
    return ['Department', 'Employee Count'];
  }

  public function title(): string
  {
    return 'Departments';
  }
}

class RecentActivitySheet implements FromArray, WithHeadings, WithTitle
{
  protected $activities;

  public function __construct($activities)
  {
    $this->activities = $activities;
  }

  public function array(): array
  {
    return collect($this->activities)->map(function ($activity) {
      return [
        $activity['name'],
        $activity['action'],
        $activity['time'],
      ];
    })->toArray();
  }

  public function headings(): array
  {
    return ['Name', 'Action', 'Time'];
  }

  public function title(): string
  {
    return 'Recent Hires';
  }
}
