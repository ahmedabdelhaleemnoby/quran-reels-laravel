<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
  /**
   * Get all notifications for the authenticated user.
   */
  public function index()
  {
    $user = Auth::user();
    $notifications = $user->notifications()->take(20)->get();
    $unreadCount = $user->unreadNotifications()->count();

    return response()->json([
      'success' => true,
      'data' => [
        'notifications' => $notifications,
        'unread_count' => $unreadCount
      ]
    ]);
  }

  /**
   * Mark a specific notification as read.
   */
  public function markAsRead($id)
  {
    $user = Auth::user();
    $notification = $user->notifications()->where('id', $id)->first();

    if ($notification) {
      $notification->markAsRead();
    }

    return response()->json([
      'success' => true,
      'message' => __('messages.success.updated')
    ]);
  }

  /**
   * Mark all notifications as read.
   */
  public function markAllAsRead()
  {
    Auth::user()->unreadNotifications->markAsRead();

    return response()->json([
      'success' => true,
      'message' => __('messages.success.updated')
    ]);
  }
}
