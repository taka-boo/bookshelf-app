<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Models\Genre;
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

    /** @test */
    public function 書籍一覧でキーワード検索ができる()
    {
        $book = Book::factory()->create(['title' => '検索テスト書籍']);

        $response = $this->get('/books?keyword=検索テスト');

        $response->assertStatus(200);
        $response->assertSee('検索テスト書籍');
    }

    /** @test */
    public function 書籍一覧でジャンル絞り込みができる()
    {
        $genre = \App\Models\Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->get("/books?genre={$genre->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function 書籍一覧でソートができる()
    {
        Book::factory()->create();

        $response = $this->get('/books?sort=title');

        $response->assertStatus(200);
    }

    /** @test */
    public function 書籍一覧で評価順ソートができる()
    {
        Book::factory()->create();

        $response = $this->get('/books?sort=rating');

        $response->assertStatus(200);
    }

    /** @test */
    public function 書籍一覧で古い順ソートができる()
    {
        Book::factory()->create();

        $response = $this->get('/books?sort=oldest');

        $response->assertStatus(200);
    }

    /** @test */
    public function ISBN検索で書籍情報を取得できる()
    {
        $user = User::factory()->create();

        \Illuminate\Support\Facades\Http::fake([
            'www.googleapis.com/*' => \Illuminate\Support\Facades\Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                            'authors' => ['テスト著者'],
                            'description' => 'テスト説明',
                            'publishedDate' => '2026-01-01',
                            'imageLinks' => ['thumbnail' => 'https://example.com/image.jpg'],
                        ],
                    ]
                ],
            ]),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784101010014');

        $response->assertStatus(200);
        $response->assertJson(['title' => 'テスト書籍']);
    }

    /** @test */
    public function ISBN検索で書籍が見つからない場合エラーを返す()
    {
        $user = User::factory()->create();

        \Illuminate\Support\Facades\Http::fake([
            'www.googleapis.com/*' => \Illuminate\Support\Facades\Http::response([
                'totalItems' => 0,
            ]),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9999999999999');

        $response->assertStatus(200);
        $response->assertJson(['error' => '書籍情報が見つかりませんでした。']);
    }

    /** @test */
    public function 存在しない書籍にアクセスすると404になる()
    {
        $response = $this->get('/books/99999');

        $response->assertStatus(404);
    }
}
