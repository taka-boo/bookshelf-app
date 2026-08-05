<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍は登録者を取得できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $book->user);
        $this->assertSame($user->id, $book->user->id);
    }

    /** @test */
    public function 書籍はジャンルを取得できる()
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);

        $this->assertTrue($book->genres->contains($genre));
    }

    /** @test */
    public function 書籍はレビューを取得できる()
    {
        $book = Book::factory()->create();
        Review::factory()->create(['book_id' => $book->id]);

        $this->assertCount(1, $book->reviews);
    }
}
