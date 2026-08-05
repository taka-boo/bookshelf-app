<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍一覧を取得できる()
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 4]);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                ['id', 'title', 'author', 'isbn', 'genres', 'reviews_avg_rating', 'reviews_count'],
            ],
            'links',
            'meta',
        ]);
    }

    /** @test */
    public function キーワードで書籍を検索できる()
    {
        Book::factory()->create(['title' => '検索対象の本']);
        Book::factory()->create(['title' => '別の本']);

        $response = $this->getJson('/api/v1/books?keyword=検索対象');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => '検索対象の本']);
    }

    /** @test */
    public function ジャンルで書籍を絞り込める()
    {
        $genre = Genre::factory()->create();
        $target = Book::factory()->create(['title' => '対象の本']);
        $target->genres()->attach($genre->id);
        Book::factory()->create(['title' => '対象外の本']);

        $response = $this->getJson("/api/v1/books?genre_id={$genre->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => '対象の本']);
    }

    /** @test */
    public function 件数の上限を超えるとバリデーションエラーになる()
    {
        $response = $this->getJson('/api/v1/books?per_page=200');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    /** @test */
    public function 書籍詳細を取得できる()
    {
        $user = User::factory()->create(['name' => 'レビュー投稿者']);
        $book = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.reviews.0.user_name', 'レビュー投稿者');
    }

    /** @test */
    public function 存在しない_i_dを指定すると404になる()
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404);
        $response->assertJsonStructure(['error']);
    }

    /** @test */
    public function 書籍を登録できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->postJson('/api/v1/books', [
            'user_id' => $user->id,
            'title' => 'API登録の本',
            'author' => 'API著者',
            'isbn' => '9781234567897',
            'published_date' => '2026-01-01',
            'description' => 'API経由で登録',
            'image_url' => 'https://example.com/api.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', ['title' => 'API登録の本']);
    }

    /** @test */
    public function 不正な入力では書籍を登録できない()
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => '',
            'isbn' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id', 'title', 'author', 'isbn', 'published_date', 'genres']);
    }

    /** @test */
    public function 書籍を更新できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => 'API更新後',
            'author' => '更新著者',
            'isbn' => $book->isbn,
            'published_date' => '2026-02-02',
            'description' => '更新しました',
            'image_url' => 'https://example.com/updated.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'API更新後',
        ]);
    }

    /** @test */
    public function 書籍を削除できる()
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
