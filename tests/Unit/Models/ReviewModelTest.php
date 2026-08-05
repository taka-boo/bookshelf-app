<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function レビューは投稿者を取得できる()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertSame($user->id, $review->user->id);
    }

    /** @test */
    public function レビューは書籍を取得できる()
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertSame($book->id, $review->book->id);
    }

    /** @test */
    public function レビューはいいねしたユーザーを取得できる()
    {
        $review = Review::factory()->create();
        $user = User::factory()->create();
        $review->likedByUsers()->attach($user->id);

        $this->assertTrue($review->likedByUsers->contains($user));
    }
}
