<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    // PG01: 書籍一覧 (トップ)
    public function index()
    {
        $books = Book::latest('id')->paginate(10);

        return view('books.index', compact('books'));
    }

    // PG02: 書籍詳細
    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    // PG03: 書籍登録画面の表示
    public function create()
    {
        $genres = Genre::orderBy('name')->get();
        $bookGenreIds = [];

        return view('books.create', compact('genres', 'bookGenreIds'));
    }

    // PG03: 書籍登録 (登録処理)
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        $book = Book::create($validated);
        $book->genres()->sync($validated['genres']);

        return redirect()->route('books.show', $book)->with('success', '書籍を登録しました。');
    }

    // PG04: 書籍編集画面の表示
    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $genres = Genre::orderBy('name')->get();
        $bookGenreIds = $book->genres->pluck('id')->toArray();

        return view('books.edit', compact('book', 'genres', 'bookGenreIds'));
    }

    // PG04: 書籍編集（更新処理）
    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $book->update($validated);
        $book->genres()->sync($validated['genres']);

        return redirect()->route('books.show', $book)->with('success', '書籍を更新しました。');
    }

    // 書籍削除
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍を削除しました。');
    }
}
