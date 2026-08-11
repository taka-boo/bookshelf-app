<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ReadingPlan $readingPlan,
        public string $timing
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    /** 通知配信方法 **/
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    /** DBに保存する内容 **/
    public function toArray(object $notifiable): array
    {
        $bookTitle = $this->readingPlan->book->title;
        $targetDate = $this->readingPlan->target_date->format('Y-m-d');

        $body = match ($this->timing) {
            'three_days_before' => "「{$bookTitle}」の読書期日（{$targetDate}）まで、あと3日です。",
            'on_due_date' => "「{$bookTitle}」の読書期日は本日（{$targetDate}）です。",
            'three_days_after' => "「{$bookTitle}」の読書期日（{$targetDate}）から3日が過ぎました。",
            default => "「{$bookTitle}」の読書期日のお知らせです。",
        };

        return [
            'reading_plan_id' => $this->readingPlan->id,
            'title' => '読書計画リマインダー',
            'body' => $body,
            'timing' => $this->timing,
        ];
    }
}
