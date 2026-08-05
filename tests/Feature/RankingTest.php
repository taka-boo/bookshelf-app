<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 平均評価の高い順に表示される()
    {
        $highBook = Book::factory()->create(['title' => '高評価の本']);
        $lowBook = Book::factory()->create(['title' => '低評価の本']);

        Review::factory()->create(['book_id' => $highBook->id, 'rating' => 5]);
        Review::factory()->create(['book_id' => $lowBook->id, 'rating' => 1]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['高評価の本', '低評価の本']);
    }

    /** @test */
    public function レビューがない書籍は表示されない()
    {
        $reviewedBook = Book::factory()->create(['title' => 'レビューあり']);
        Book::factory()->create(['title' => 'レビューなし']);

        Review::factory()->create(['book_id' => $reviewedBook->id, 'rating' => 4]);

        $response = $this->get('/ranking');

        $response->assertSee('レビューあり');
        $response->assertDontSee('レビューなし');
    }

    /** @test */
    public function レビューが複数ある場合は平均で並ぶ()
    {
        $bookA = Book::factory()->create(['title' => '平均4点の本']);
        $bookB = Book::factory()->create(['title' => '平均2点の本']);

        Review::factory()->create(['book_id' => $bookA->id, 'rating' => 5]);
        Review::factory()->create(['book_id' => $bookA->id, 'rating' => 3]);
        Review::factory()->create(['book_id' => $bookB->id, 'rating' => 3]);
        Review::factory()->create(['book_id' => $bookB->id, 'rating' => 1]);

        $response = $this->get('/ranking');

        $response->assertSeeInOrder(['平均4点の本', '平均2点の本']);
    }
}
