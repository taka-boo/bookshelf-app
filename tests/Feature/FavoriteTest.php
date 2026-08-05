<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お気に入りに追加できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post("/books/{$book->id}/favorite");

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function もう一度押すとお気に入りが解除される()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->favoriteBooks()->attach($book->id);

        $this->actingAs($user)->post("/books/{$book->id}/favorite");

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function お気に入り一覧に登録した書籍が表示される()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'お気に入りの本']);
        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
        $response->assertSee('お気に入りの本');
    }

    /** @test */
    public function 未ログインではお気に入り操作ができない()
    {
        $book = Book::factory()->create();

        $response = $this->post("/books/{$book->id}/favorite");

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('favorites', 0);
    }
}
