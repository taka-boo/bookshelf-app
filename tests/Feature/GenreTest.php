<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログイン済ユーザーはジャンルを登録できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', [
            'name' => 'テストジャンル',
        ]);

        $this->assertDatabaseHas('genres', ['name' => 'テストジャンル']);
        $response->assertRedirect('/genres');
    }

    /** @test */
    public function 重複するジャンル名は登録できない()
    {
        $user = User::factory()->create();
        Genre::factory()->create(['name' => '既存ジャンル']);

        $response = $this->actingAs($user)->post('/genres', [
            'name' => '既存ジャンル',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('genres', 1);
    }

    /** @test */
    public function ジャンルを更新できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '変更前']);

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", [
            'name' => '変更後',
        ]);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '変更後',
        ]);
        $response->assertRedirect('/genres');
    }

    /** @test */
    public function 書籍が紐づいていないジャンルは削除できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)->delete("/genres/{$genre->id}");

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    /** @test */
    public function 書籍が紐づいているジャンルは削除できない()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
        $response->assertSessionHas('error');
    }

    /** @test */
    public function ジャンル一覧を表示できる()
    {
        $user = User::factory()->create();
        Genre::factory()->create(['name' => '表示テストジャンル']);

        $response = $this->actingAs($user)->get('/genres');

        $response->assertStatus(200);
        $response->assertSee('表示テストジャンル');
    }

    /** @test */
    public function ジャンル詳細を表示できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['title' => 'ジャンル所属の本']);
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->get("/genres/{$genre->id}");

        $response->assertStatus(200);
        $response->assertSee('ジャンル所属の本');
    }

    /** @test */
    public function ジャンル登録画面を表示できる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/genres/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function ジャンル編集画面を表示できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '編集対象ジャンル']);

        $response = $this->actingAs($user)->get("/genres/{$genre->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('編集対象ジャンル');
    }
}
