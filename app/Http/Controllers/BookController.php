<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class BookController extends Controller
{
    // PG01: 書籍一覧 (トップ)
    public function index(Request $request): View
    {
        $query = Book::with('genres')
            ->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->input('genre'));
            });
        }

        switch ($request->input('sort')) {
            case 'oldest':
                $query->oldest('id');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'rating':
                $query->orderByRaw('reviews_avg_rating IS NULL')
                    ->orderByDesc('reviews_avg_rating');
                break;
            default:
                $query->latest('id');
                break;
        }

        $books = $query->paginate(10)->withQueryString();
        $genres = Genre::orderBy('name')->get();

        return view('books.index', compact('books', 'genres'));
    }

    // PG02: 書籍詳細
    public function show(Book $book): View
    {
        return view('books.show', compact('book'));
    }

    // PG03: 書籍登録画面の表示
    public function create(): View
    {
        $genres = Genre::orderBy('name')->get();
        $bookGenreIds = [];

        return view('books.create', compact('genres', 'bookGenreIds'));
    }

    // PG03: 書籍登録 (登録処理)
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        $book = Book::create($validated);
        $book->genres()->sync($validated['genres']);

        return redirect()->route('books.show', $book)->with('success', '書籍を登録しました。');
    }

    // PG04: 書籍編集画面の表示
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::orderBy('name')->get();
        $bookGenreIds = $book->genres->pluck('id')->toArray();

        return view('books.edit', compact('book', 'genres', 'bookGenreIds'));
    }

    // PG04: 書籍編集（更新処理）
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $book->update($validated);
        $book->genres()->sync($validated['genres']);

        return redirect()->route('books.show', $book)->with('success', '書籍を更新しました。');
    }

    // 書籍削除
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍を削除しました。');
    }

    // ISBN-13でGoogle Books APIから書籍情報を取得
    public function searchByIsbn(string $isbn): JsonResponse
    {
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:'.$isbn,
            'key' => config('services.google_books.api_key'),
        ]);

        $data = $response->json();

        if (empty($data['items'])) {
            return response()->json(['error' => '書籍情報が見つかりませんでした。']);
        }

        $book = $data['items'][0]['volumeInfo'];

        return response()->json([
            'title' => $book['title'] ?? '',
            'author' => isset($book['authors']) ? implode(', ', $book['authors']) : '',
            'description' => $book['description'] ?? '',
            'published_date' => $book['publishedDate'] ?? '',
            'image_url' => $book['imageLinks']['thumbnail'] ?? '',
        ]);
    }
}
