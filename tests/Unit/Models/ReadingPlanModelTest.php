<?php

namespace Tests\Unit\Models;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 読書計画は計画者を取得できる()
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $plan->user);
        $this->assertSame($user->id, $plan->user->id);
    }

    /** @test */
    public function 読書計画は書籍を取得できる()
    {
        $book = Book::factory()->create();
        $plan = ReadingPlan::factory()->create(['book_id' => $book->id]);

        $this->assertInstanceOf(Book::class, $plan->book);
        $this->assertSame($book->id, $plan->book->id);
    }

    /** @test */
    public function 読書計画の状態はEnumでキャストされる()
    {
        $plan = ReadingPlan::factory()->create(['status' => 'in_progress']);

        $this->assertInstanceOf(ReadingPlanStatus::class, $plan->status);
        $this->assertSame('進行中', $plan->status->label());
    }

    /** @test */
    public function Enumのバッジクラスが正しく返る()
    {
        $this->assertSame('bg-blue-100 text-blue-800', ReadingPlanStatus::InProgress->badgeClass());
        $this->assertSame('bg-green-100 text-green-800', ReadingPlanStatus::Completed->badgeClass());
        $this->assertSame('bg-red-100 text-red-800', ReadingPlanStatus::Expired->badgeClass());
    }
}