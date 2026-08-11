<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function マイ読書レポートを表示できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
    }

    /** @test */
    public function レビューがある場合に統計が表示される()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 5]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
        $response->assertSee('1');
        $response->assertSee('5.0');
    }

    /** @test */
    public function レビューが無くてもエラーにならない()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
        $response->assertSee('0');
    }

    /** @test */
    public function 未ログインではレポートにアクセスできない()
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }
}