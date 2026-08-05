<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログイン済ユーザーがレビューを投稿できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'comment' => '素晴らしい書籍でした！',
            'rating' => 5,
        ]);

        $response->assertRedirect("/books/{$book->id}");
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => '素晴らしい書籍でした！',
            'rating' => 5,
        ]);
    }

    /** @test */
    public function 評価が範囲外だとバリデーションエラーになる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'comment' => '評価が範囲外です。',
            'rating' => 6, // 範囲外の評価
        ]);

        $response->assertSessionHasErrors(['rating']);
        $this->assertDatabaseCount('reviews', 0);
    }

    /** @test */
    public function 自分のレビューを更新できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
            'comment' => 'レビューを更新しました。',
            'rating' => 4,
        ]);

        $response->assertRedirect("/books/{$book->id}");
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => 'レビューを更新しました。',
            'rating' => 4,
        ]);
    }

    /** @test */
    public function 自分のレビューを削除できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/reviews/{$review->id}");
        $response->assertRedirect("/books/{$book->id}");
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function 他人のレビューは更新できない()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id, 'user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
            'comment' => '不正な更新',
            'rating' => 1,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
            'comment' => '不正な更新',
        ]);
    }
}
