<?php

namespace Database\Seeders;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $users = User::all();

    foreach ($users as $user) {
      $user->notify(new GeneralNotification([
        'type' => 'leave',
        'name' => 'Ahmed Ali',
        'message' => 'New leave request from Ahmed Ali',
      ]));

      $user->notify(new GeneralNotification([
        'type' => 'attendance',
        'name' => 'Sara Smith',
        'message' => 'Attendance alert for Sara Smith',
      ]));

      $user->notify(new GeneralNotification([
        'type' => 'payroll',
        'month' => 'January',
        'message' => 'Payroll for January has been processed',
      ]));

      $user->notify(new GeneralNotification([
        'type' => 'candidate',
        'job' => 'Frontend Developer',
        'message' => 'New candidate for Frontend Developer position',
      ]));
    }
  }
}
