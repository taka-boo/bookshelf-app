<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GenreController extends Controller
{
    // PG05: ジャンル一覧
    public function index(): View
    {
        $genres = Genre::withCount('books')->orderBy('name')->get();

        return view('genres.index', compact('genres'));
    }

    // PG06: ジャンル詳細
    public function show(Genre $genre): View
    {
        $books = $genre->books()->with('genres')->latest()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    // PG07: ジャンル登録画面
    public function create(): View
    {
        return view('genres.create');
    }

    // PG07: ジャンル登録
    public function store(StoreGenreRequest $request): RedirectResponse
    {
        Genre::create($request->validated());

        if ($request->query('from') === 'books.create') {
            return redirect()->route('books.create')->with('success', 'ジャンルを登録しました。');
        }

        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました。');
    }

    // PG08: ジャンル編集画面
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    // PG08: ジャンル編集（更新処理）
    public function update(UpdateGenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを更新しました。');
    }

    // ジャンル削除
    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {
            return redirect()->route('genres.index')->with('error', '書籍が紐づいているジャンルは削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')->with('removed', 'ジャンルを削除しました。');
    }
}
