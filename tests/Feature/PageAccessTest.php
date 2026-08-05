<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍一覧は誰でもアクセスできる()
    {
        $response = $this->get('/books');

        $response->assertStatus(200);
    }

    /** @test */
    public function 書籍詳細は誰でもアクセスできる()
    {
        $book = Book::factory()->create();
        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function ランキングは誰でもアクセスできる()
    {
        $response = $this->get('/ranking');

        $response->assertStatus(200);
    }

    /** @test */
    public function 未ログインで書籍登録画面にアクセスするとログイン画面にリダイレクトされる()
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function ログイン済なら書籍登録画面にアクセスできる()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/books/create');

        $response->assertStatus(200);
    }
}
