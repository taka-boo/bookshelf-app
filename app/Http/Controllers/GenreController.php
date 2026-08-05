<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;

class GenreController extends Controller
{
    // PG05: ジャンル一覧
    public function index()
    {
        $genres = Genre::withCount('books')->orderBy('name')->get();

        return view('genres.index', compact('genres'));
    }

    // PG06: ジャンル詳細
    public function show(Genre $genre)
    {
        $books = $genre->books()->with('genres')->latest()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    // PG07: ジャンル登録画面
    public function create()
    {
        return view('genres.create');
    }

    // PG07: ジャンル登録
    public function store(StoreGenreRequest $request)
    {
        Genre::create($request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました。');
    }

    // PG08: ジャンル編集画面
    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    // PG08: ジャンル編集（更新処理）
    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $genre->update($request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを更新しました。');
    }

    // ジャンル削除
    public function destroy(Genre $genre)
    {
        if ($genre->books()->exists()) {
            return redirect()->route('genres.index')->with('error', '書籍が紐づいているジャンルは削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました。');
    }
}
