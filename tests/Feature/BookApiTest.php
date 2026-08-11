<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍を登録できる()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $genre = Genre::factory()->create();

        $response = $this->postJson('/api/v1/books', [
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
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => '',
            'isbn' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'author', 'isbn', 'published_date', 'genres']);
    }

    /** @test */
    public function 書籍を更新できる()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
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
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /** @test */
    public function ap_iでキーワード検索とジャンル絞り込みを同時に使える()
    {
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['title' => 'API検索テスト']);
        $book->genres()->attach($genre->id);

        $response = $this->getJson("/api/v1/books?keyword=API検索&genre_id={$genre->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function ap_iで書籍詳細にレビューと投稿者名が含まれる()
    {
        $user = User::factory()->create(['name' => 'APIテスト投稿者']);
        $book = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.reviews.0.user_name', 'APIテスト投稿者');
        $response->assertJsonStructure([
            'data' => [
                'reviews' => [
                    ['id', 'rating', 'comment', 'user_name', 'created_at'],
                ],
            ],
        ]);
    }

    /** @test */
    public function 未認証で_ap_iの書き込みは401になる()
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function 他人の書籍を_ap_iで更新すると403になる()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($other);
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $genre = Genre::factory()->create();

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '不正な更新',
            'author' => '不正',
            'isbn' => $book->isbn,
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function ap_iで存在しない_i_dにアクセスすると日本語エラーの_jso_nが返る()
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404);
        $response->assertJson(['error' => '書籍が見つかりませんでした']);
    }
}
