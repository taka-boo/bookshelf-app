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
            'three_days_before' => "「{$bookTitle}」の目標日({$targetDate}）まであと3日です。",
            'on_due_date' => "「{$bookTitle}」の目標日は本日({$targetDate}）です。",
            'three_days_after' => "「{$bookTitle}」の目標日({$targetDate}）から３日過ぎています。",
            default => "「{$bookTitle}」の目標日({$targetDate}）のお知らせです。",
        };

        return [
            'reading_plan_id' => $this->readingPlan->id,
            'book_title' => $this->readingPlan->book->title,
            'body' => $body,
            'timing' => $this->timing,
        ];
    }
}
