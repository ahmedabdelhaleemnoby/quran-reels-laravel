<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeaveRequestNotification extends Notification
{
  use Queueable;

  public $leaveRequest;

  /**
   * Create a new notification instance.
   */
  public function __construct($leaveRequest)
  {
    $this->leaveRequest = $leaveRequest;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['database']; // Keeping it simple with database first
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail(object $notifiable): MailMessage
  {
    return (new MailMessage)
      ->subject('New Leave Request from ' . $this->leaveRequest->employee->full_name)
      ->line('A new leave request has been submitted.')
      ->line('Type: ' . $this->leaveRequest->leaveType->name)
      ->line('Dates: ' . $this->leaveRequest->start_date . ' to ' . $this->leaveRequest->end_date)
      ->action('View Request', url('/leave-approvals')) // We will create this page
      ->line('Please approve or reject this request.');
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return [
      'leave_request_id' => $this->leaveRequest->id,
      'employee_name' => $this->leaveRequest->employee->full_name,
      'leave_type' => $this->leaveRequest->leaveType->name,
      'days' => $this->leaveRequest->total_days,
      'start_date' => $this->leaveRequest->start_date,
      'end_date' => $this->leaveRequest->end_date,
      'message' => 'New leave request from ' . $this->leaveRequest->employee->full_name,
      'type' => 'leave_request'
    ];
  }
}
