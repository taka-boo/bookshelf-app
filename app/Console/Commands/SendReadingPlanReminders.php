<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendReadingPlanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plans:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '目標日の期限切れ処理とリマインダー通知を実行する';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->startOfDay();

        DB::transaction(function () use ($today) {
            // 目標日が過ぎた未完了のReadingPlanを期限切れにする
            ReadingPlan::where('status', ReadingPlanStatus::InProgress)
                ->whereDate('target_date', '<', $today)
                ->get()
                ->each(function (ReadingPlan $plan) {
                    $plan->update(['status' => ReadingPlanStatus::Expired]);
                });

            // 目標日まであと3日のReadingPlanにリマインダー通知を送信する
            $timings = [
                'three_days_before' => $today->copy()->addDays(3),
                'on_due_date' => $today,
                'three_days_after' => $today->copy()->subDays(3),
            ];
            collect($timings)->each(function ($date, $timing) {
                ReadingPlan::with('user', 'book')
                    ->whereDate('target_date', $date)
                    ->get()
                    ->each(function (ReadingPlan $plan) use ($timing) {
                        $plan->user->notify(new ReadingPlanReminder($plan, $timing));
                    });
            });
        });

        $this->info('読書計画のリマインダー処理が完了しました。');

        return Command::SUCCESS;
    }
}
