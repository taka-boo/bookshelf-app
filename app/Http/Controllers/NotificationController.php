<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * 通知一覧を表示する
     */
    public function index(): View
    {
        $notifications = auth()->user()->notifications;

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 指定した通知を既読にする
     */
    public function read(string $id): RedirectResponse
    {
        $notification = auth()->user()
            ->unreadNotifications
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return redirect()->route('notifications.index')
            ->with('success', '通知を既読にしました。');
    }

    /**
     * 指定した通知を未読に戻す
     */
    public function unread(string $id): RedirectResponse
    {
        $notification = auth()->user()
            ->notifications
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->update(['read_at' => null]);
        }

        return redirect()->route('notifications.index')
            ->with('removed', '通知を未読に戻しました。');
    }
}
