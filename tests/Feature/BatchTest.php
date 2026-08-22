<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 期日超過の計画が期限切れになる()
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => now()->subDays(1),
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'expired',
        ]);
    }

    /** @test */
    public function 期日当日の計画にリマインダー通知が送られる()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->format('Y-m-d'),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
        ]);
    }

    /** @test */
    public function 読了済みの計画は期限切れにならない()
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Completed,
            'target_date' => now()->subDays(1),
            'completed_at' => now()->subDays(2),
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function バッチを2回実行しても期限切れ状態への変更は冪等である()
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => now()->subDays(1),
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();
        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'expired',
        ]);
        $this->assertDatabaseCount('reading_plans', 1);
    }

    /** @test */
    public function バッチを2回実行すると同じ計画に通知が重複して作成される()
    {
        // 現状の実装(SendReadingPlanReminders)には送信済み通知の重複チェックが無いため、
        // 同じ対象日に対して複数回実行すると通知が重複して作成される。
        // このテストは「冪等ではない」という現状の挙動を明示的に記録するもので、
        // 将来重複防止ロジックが実装された際にはこのテストを更新する必要がある。
        $user = User::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->format('Y-m-d'),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();
        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 2);
    }

    /** @test */
    public function 目標日の3日前ちょうどでリマインダー通知が送られる()
    {
        $user = User::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
        ]);
        $this->assertDatabaseCount('notifications', 1);
    }

    /** @test */
    public function 目標日の2日前では_3日前リマインダーは送られない()
    {
        $user = User::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(2)->format('Y-m-d'),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function 目標日の4日前では_3日前リマインダーは送られない()
    {
        $user = User::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(4)->format('Y-m-d'),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 0);
    }
}
