<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class DailySummaryMail extends Mailable
{
  use Queueable, SerializesModels;

  public $absentEmployees;
  public $expiringContracts;
  public $birthdays;
  public $probationEndings;
  public $date;

  public function __construct($absentEmployees, $expiringContracts, $birthdays, $probationEndings, Carbon $date)
  {
    $this->absentEmployees = $absentEmployees;
    $this->expiringContracts = $expiringContracts;
    $this->birthdays = $birthdays;
    $this->probationEndings = $probationEndings;
    $this->date = $date;
  }

  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Daily HR Summary - ' . $this->date->format('F d, Y'),
    );
  }

  public function content(): Content
  {
    return new Content(
      view: 'emails.daily-summary',
    );
  }

  public function attachments(): array
  {
    return [];
  }
}
