<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ジャンルは書籍を取得できる()
    {
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $genre->books()->attach($book->id);

        $this->assertTrue($genre->books->contains($book));
    }
}
