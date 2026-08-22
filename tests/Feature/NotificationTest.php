<?php

namespace Tests\Feature;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createNotificationForUser(User $user, string $timing = 'on_due_date'): void
    {
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $plan->load('book');
        $user->notify(new ReadingPlanReminder($plan, $timing));
    }

    /** @test */
    public function 通知一覧を表示できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertStatus(200);
    }

    /** @test */
    public function 通知を既読にできる()
    {
        $user = User::factory()->create();
        $this->createNotificationForUser($user);

        $notification = $user->unreadNotifications->first();

        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/read");

        $response->assertRedirect('/notifications');
        $this->assertNotNull($notification->fresh()->read_at);
    }

    /** @test */
    public function 通知を未読にできる()
    {
        $user = User::factory()->create();
        $this->createNotificationForUser($user);

        $notification = $user->unreadNotifications->first();
        $notification->markAsRead(); // 先に既読にしておく

        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/unread");

        $response->assertRedirect('/notifications');
        $this->assertNull($notification->fresh()->read_at);
    }

    /** @test */
    public function 通知にはタイトルと本文とタイミングが含まれる()
    {
        $user = User::factory()->create();
        $this->createNotificationForUser($user, 'three_days_before');

        $notification = $user->notifications()->first();

        $this->assertSame('読書計画リマインダー', $notification->data['title']);
        $this->assertSame('three_days_before', $notification->data['timing']);
        $this->assertNotEmpty($notification->data['body']);
    }

    /** @test */
    public function 未ログインでは通知一覧にアクセスできない()
    {
        $response = $this->get('/notifications');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 他人の通知を既読にしようとしても対象の通知は既読にならない()
    {
        // NotificationController::read()はauth()->user()->unreadNotificationsで
        // 自分の通知のみに絞り込んでいるため、他人の通知IDを渡しても
        // 該当レコードが見つからず何もせずにリダイレクトする(403は返らない)。
        // ここでは「他人の通知の既読状態が変化しないこと」を検証する。
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $this->createNotificationForUser($owner);
        $notification = $owner->unreadNotifications->first();

        $response = $this->actingAs($attacker)->post("/notifications/{$notification->id}/read");

        $response->assertRedirect('/notifications');
        $this->assertNull($notification->fresh()->read_at);
    }
}
