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
    public function 同じ書籍に2回目のレビューは投稿できない()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Review::factory()->create(['book_id' => $book->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'comment' => '2回目の投稿です。',
            'rating' => 3,
        ]);

        $response->assertSessionHasErrors(['rating']);
        $this->assertSame(
            'この書籍には既にレビューを投稿済みです。',
            session('errors')->first('rating')
        );
        $this->assertDatabaseCount('reviews', 1);
    }

    /** @test */
    public function 別のユーザーであれば同じ書籍にレビューできる()
    {
        $book = Book::factory()->create();
        $existingReviewer = User::factory()->create();
        Review::factory()->create(['book_id' => $book->id, 'user_id' => $existingReviewer->id]);

        $anotherUser = User::factory()->create();
        $response = $this->actingAs($anotherUser)->post("/books/{$book->id}/reviews", [
            'comment' => '別ユーザーからのレビューです。',
            'rating' => 4,
        ]);

        $response->assertRedirect("/books/{$book->id}");
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $anotherUser->id,
        ]);
        $this->assertDatabaseCount('reviews', 2);
    }

    /** @test */
    public function 同じユーザーでも別の書籍にはレビューできる()
    {
        $user = User::factory()->create();
        $firstBook = Book::factory()->create();
        Review::factory()->create(['book_id' => $firstBook->id, 'user_id' => $user->id]);

        $secondBook = Book::factory()->create();
        $response = $this->actingAs($user)->post("/books/{$secondBook->id}/reviews", [
            'comment' => '別の書籍へのレビューです。',
            'rating' => 5,
        ]);

        $response->assertRedirect("/books/{$secondBook->id}");
        $this->assertDatabaseHas('reviews', [
            'book_id' => $secondBook->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseCount('reviews', 2);
    }

    /** @test */
    public function 評価が範囲外だとバリデーションエラーになる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'comment' => '評価が範囲外です。',
            'rating' => 6, // 範囲外の評価(上限超過)
        ]);

        $response->assertSessionHasErrors(['rating']);
        $this->assertDatabaseCount('reviews', 0);
    }

    /** @test */
    public function 評価が0だとバリデーションエラーになる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'comment' => '評価が範囲外です。',
            'rating' => 0, // 範囲外の評価(下限未満)
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

    /** @test */
    public function 他人のレビューは削除できない()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id, 'user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete("/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
