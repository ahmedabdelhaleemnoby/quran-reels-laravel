<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daily HR Summary</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      line-height: 1.6;
      color: #333;
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
    }

    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px;
      text-align: center;
      border-radius: 8px 8px 0 0;
    }

    .content {
      background: #f8f9fa;
      padding: 30px;
      border-radius: 0 0 8px 8px;
    }

    .section {
      background: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 6px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .section-title {
      color: #667eea;
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 2px solid #667eea;
    }

    .employee-list {
      list-style: none;
      padding: 0;
    }

    .employee-item {
      padding: 10px;
      margin-bottom: 8px;
      background: #f8f9fa;
      border-left: 3px solid #667eea;
      border-radius: 4px;
    }

    .employee-name {
      font-weight: bold;
      color: #333;
    }

    .employee-details {
      font-size: 14px;
      color: #666;
      margin-top: 4px;
    }

    .badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: bold;
    }

    .badge-danger {
      background: #fee;
      color: #c33;
    }

    .badge-warning {
      background: #fff3cd;
      color: #856404;
    }

    .empty-state {
      text-align: center;
      color: #999;
      padding: 20px;
      font-style: italic;
    }

    .footer {
      text-align: center;
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #ddd;
      color: #999;
      font-size: 12px;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>📊 Daily HR Summary</h1>
    <p>{{ $date->format('l, F d, Y') }}</p>
  </div>

  <div class="content">
    <!-- Absent Employees Section -->
    <div class="section">
      <div class="section-title">
        🚫 Absent Employees ({{ $absentEmployees->count() }})
      </div>

      @if($absentEmployees->count() > 0)
        <ul class="employee-list">
          @foreach($absentEmployees as $employee)
            <li class="employee-item">
              <div class="employee-name">
                {{ $employee->first_name }} {{ $employee->last_name }}
                <span class="badge badge-danger">ABSENT</span>
              </div>
              <div class="employee-details">
                {{ $employee->department ?? 'No Department' }} · {{ $employee->position ?? 'No Position' }}
              </div>
            </li>
          @endforeach
        </ul>
      @else
        <div class="empty-state">
          ✅ All employees are present today!
        </div>
      @endif
    </div>

    <!-- Birthdays Today Section -->
    @if($birthdays->count() > 0)
      <div class="section" style="border-left: 5px solid #ff69b4;">
        <div class="section-title" style="color: #ff69b4; border-bottom-color: #ff69b4;">
          🎂 Birthdays Today ({{ $birthdays->count() }})
        </div>
        <ul class="employee-list">
          @foreach($birthdays as $employee)
            <li class="employee-item" style="border-left-color: #ff69b4;">
              <div class="employee-name">
                {{ $employee->first_name }} {{ $employee->last_name }}
                <span class="badge" style="background: #ffe4e1; color: #d02090;">BIRTHDAY</span>
              </div>
              <div class="employee-details">
                Wishing {{ $employee->first_name }} a fantastic birthday!
              </div>
            </li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- Probation Ending Section -->
    @if($probationEndings->count() > 0)
      <div class="section" style="border-left: 5px solid #20b2aa;">
        <div class="section-title" style="color: #20b2aa; border-bottom-color: #20b2aa;">
          🛡️ Probation Ending ({{ $probationEndings->count() }})
        </div>
        <ul class="employee-list">
          @foreach($probationEndings as $employee)
            @php
              $daysLeft = \Carbon\Carbon::today()->diffInDays($employee->probation_end_date);
            @endphp
            <li class="employee-item" style="border-left-color: #20b2aa;">
              <div class="employee-name">
                {{ $employee->first_name }} {{ $employee->last_name }}
                <span class="badge" style="background: #e0ffff; color: #008080;">{{ $daysLeft }} days left</span>
              </div>
              <div class="employee-details">
                Probation ends: {{ \Carbon\Carbon::parse($employee->probation_end_date)->format('M d, Y') }}
              </div>
            </li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- Expiring Contracts Section -->
    <div class="section">
      <div class="section-title">
        ⏰ Expiring Contracts ({{ $expiringContracts->count() }})
      </div>

      @if($expiringContracts->count() > 0)
        <ul class="employee-list">
          @foreach($expiringContracts as $employee)
            @php
              $daysRemaining = \Carbon\Carbon::today()->diffInDays($employee->contract_end_date);
            @endphp
            <li class="employee-item">
              <div class="employee-name">
                {{ $employee->first_name }} {{ $employee->last_name }}
                <span class="badge badge-warning">{{ $daysRemaining }} days left</span>
              </div>
              <div class="employee-details">
                {{ $employee->department ?? 'No Department' }} · Expires:
                {{ \Carbon\Carbon::parse($employee->contract_end_date)->format('M d, Y') }}
              </div>
            </li>
          @endforeach
        </ul>
      @else
        <div class="empty-state">
          ✅ No contracts expiring in the next 30 days.
        </div>
      @endif
    </div>
  </div>

  <div class="footer">
    <p>This is an automated daily summary from your HRM Platform.</p>
    <p>Generated at {{ now()->format('H:i A') }}</p>
  </div>
</body>

</html>