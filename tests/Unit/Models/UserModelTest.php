<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ユーザーは登録した書籍を取得できる()
    {
        $user = User::factory()->create();
        Book::factory()->create(['user_id' => $user->id]);

        $this->assertCount(1, $user->books);
    }

    /** @test */
    public function ユーザーは投稿したレビューを取得できる()
    {
        $user = User::factory()->create();
        Review::factory()->create(['user_id' => $user->id]);

        $this->assertCount(1, $user->reviews);
    }

    /** @test */
    public function ユーザーはお気に入り書籍を取得できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->favoriteBooks()->attach($book->id);

        $this->assertTrue($user->favoriteBooks->contains($book));
    }

    /** @test */
    public function ユーザーはいいねしたレビューを取得できる()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();
        $user->likedReviews()->attach($review->id);

        $this->assertTrue($user->likedReviews->contains($review));
    }
}
