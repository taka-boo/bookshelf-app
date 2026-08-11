<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 読書計画一覧を表示できる()
    {
        $user = User::factory()->create();
        ReadingPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/reading-plans');

        $response->assertStatus(200);
    }

    /** @test */
    public function 状態で絞り込みができる()
    {
        $user = User::factory()->create();
        ReadingPlan::factory()->create(['user_id' => $user->id, 'status' => ReadingPlanStatus::InProgress]);
        ReadingPlan::factory()->create(['user_id' => $user->id, 'status' => ReadingPlanStatus::Completed, 'completed_at' => now()]);

        $response = $this->actingAs($user)->get('/reading-plans?status=in_progress');

        $response->assertStatus(200);
    }

    /** @test */
    public function 読書計画作成画面を表示できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reading-plans/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function 読書計画を作成できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect('/reading-plans');
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function 過去の日付では作成できない()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => '2020-01-01',
        ]);

        $response->assertSessionHasErrors(['target_date']);
    }

    /** @test */
    public function 読書計画編集画面を表示できる()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/reading-plans/{$plan->id}/edit");

        $response->assertStatus(200);
    }

    /** @test */
    public function 読書計画を更新できる()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $newDate = now()->addDays(14)->format('Y-m-d');

        $response = $this->actingAs($user)->put("/reading-plans/{$plan->id}", [
            'target_date' => $newDate,
        ]);

        $response->assertRedirect('/reading-plans');
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => $newDate,
        ]);
    }

    /** @test */
    public function 読書計画を削除できる()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/reading-plans/{$plan->id}");

        $response->assertRedirect('/reading-plans');
        $this->assertDatabaseMissing('reading_plans', ['id' => $plan->id]);
    }

    /** @test */
    public function 読書計画を読了にできる()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/reading-plans/{$plan->id}/complete");

        $response->assertRedirect('/reading-plans');
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function 読了を進行中に戻せる()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post("/reading-plans/{$plan->id}/reopen");

        $response->assertRedirect('/reading-plans');
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'in_progress',
            'completed_at' => null,
        ]);
    }

    /** @test */
    public function 他人の読書計画は編集できない()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->put("/reading-plans/{$plan->id}", [
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function 未ログインでは読書計画を作成できない()
    {
        $response = $this->post('/reading-plans', [
            'book_id' => 1,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect('/login');
    }
}
