<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログイン済ユーザーは書籍登録ができる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2023-01-01',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/test.jpg',
            'genres' => [$genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'user_id' => $user->id,
        ]);

        $book = Book::where('title', 'テスト書籍')->first();
        $response->assertRedirect("/books/{$book->id}");
    }

    /** @test */
    public function 必須項目が空だとバリデーションエラーになる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => '',
            'author' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'author', 'genres']);
        $this->assertDatabaseCount('books', 0);
    }

    /** @test */
    public function 自分の書籍を更新できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->put('/books/' . $book->id, [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => $book->isbn,
            'published_date' => '2026-02-02',
            'description' => '更新後の説明',
            'image_url' => 'https://example.com/updated.jpg',
            'genres' => [$genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
        ]);
    }

    /** @test */
    public function 自分の書籍を削除できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete('/books/' . $book->id);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $response->assertRedirect('/books');
    }

    /** @test */
    public function 他人の書籍を編集しようとすると403になる()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->get('/books/' . $book->id . '/edit');

        $response->assertStatus(403);
    }

    /** @test */
    public function 他人の書籍を更新しようとすると403になる()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->put('/books/' . $book->id, [
            'title' => '不正な更新',
            'author' => '不正な著者',
            'isbn' => $book->isbn,
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
            'title' => '不正な更新',
        ]);
    }

    /** @test */
    public function 他人の書籍を削除しようとすると403になる()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->delete('/books/' . $book->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    /** @test */
    public function 存在しない書籍の更新で404になる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put('/books/999999', [
            'title' => 'タイトル',
            'author' => '著者',
            'isbn' => '1111111111111',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function 存在しない書籍の削除で404になる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/books/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function 書籍削除時に関連するレビューお気に入りジャンル紐付けも削除される()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $book->genres()->attach($genre->id);
        $review = Review::factory()->create(['book_id' => $book->id, 'user_id' => $otherUser->id]);
        $otherUser->favoriteBooks()->attach($book->id);

        $this->actingAs($user)->delete('/books/' . $book->id);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('favorites', ['book_id' => $book->id]);
        $this->assertDatabaseMissing('book_genre', ['book_id' => $book->id]);
    }

    /** @test */
    public function 同じISBNの書籍は登録できない()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $existing = Book::factory()->create(['isbn' => '9784000000000']);

        $response = $this->actingAs($user)->post('/books', [
            'title' => '重複ISBNの本',
            'author' => 'テスト著者',
            'isbn' => $existing->isbn,
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors(['isbn']);
        $this->assertDatabaseCount('books', 1);
    }
}
