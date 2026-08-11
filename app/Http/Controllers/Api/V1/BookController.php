<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    // AP01: 書籍一覧
    public function index(IndexBookRequest $request): AnonymousResourceCollection
    {
        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->input('genre_id'));
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $books = $query->latest('id')->paginate($perPage);

        return BookResource::collection($books);
    }

    // AP02: 書籍詳細
    public function show(Book $book): BookResource
    {
        $book->load('genres', 'reviews.user')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookResource($book);
    }

    // AP03: 書籍登録
    public function store(StoreBookRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        $book = Book::create($validated);
        $book->genres()->sync($validated['genres']);

        $book->load('genres', 'reviews.user')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    // AP04: 書籍更新
    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $book->update($validated);
        $book->genres()->sync($validated['genres']);

        $book->load('genres', 'reviews.user')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookResource($book);
    }

    // AP05: 書籍削除
    public function destroy(Book $book): JsonResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json(null, 204);
    }
}
