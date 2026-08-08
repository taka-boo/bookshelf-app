<?php

namespace App\Http\Controllers;

use App\Models\Notification as DatabaseNotification;
use Illumiate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /** 通知一覧の表示 **/
    public function index(): View
    {
        $notifications = auth()->user()->notifications;

        return view('notifications.index', compact('notifications'));
    }

    /** 通知の既読処理 **/
    public function read(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->route('notifications.index')->with('success', '通知を既読にしました。');
    }
}
