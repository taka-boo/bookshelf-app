<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

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
}
