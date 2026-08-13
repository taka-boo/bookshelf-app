<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    // レビュー投稿
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['book_id'] = $book->id;

        Review::create($validated);

        return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました。');
    }

    // PG09: レビュー編集画面の表示
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    // PG09: レビュー更新
    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book)->with('success', 'レビューを更新しました。');
    }

    // レビュー削除
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)->with('removed', 'レビューを削除しました。');
    }

    // レビューへのいいねトグル
    public function like(Review $review): RedirectResponse
    {
        $user = auth()->user();

        if ($review->likedByUsers()->where('user_id', $user->id)->exists()) {
            $review->likedByUsers()->detach($user->id);

            return back()->with('removed', 'いいねを外しました。');
        } else {
            $review->likedByUsers()->attach($user->id);

            return back()->with('success', 'いいねしました。');
        }
    }

    // PG10: お気に入り一覧
    public function favorites(): View
    {
        $books = auth()->user()->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    // お気に入りトグル（追加/解除）
    public function favoriteToggle(Book $book): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if ($user->favoriteBooks()->where('book_id', $book->id)->exists()) {
            $user->favoriteBooks()->detach($book->id);
            $action = 'removed';
            $message = 'お気に入りを外しました。';
        } else {
            $user->favoriteBooks()->attach($book->id);
            $action = 'added';
            $message = 'お気に入りに追加しました。';
        }

        if (request()->expectsJson()) {
            return response()->json(['action' => $action, 'message' => $message]);
        }

        return back()->with($action === 'removed' ? 'removed' : 'success', $message);
    }

    // PG11: ランキング
    public function ranking(): View
    {
        $rankedBooks = Book::withAvg('reviews', 'rating')
            ->having('reviews_avg_rating', '>', 0)
            ->orderByDesc('reviews_avg_rating')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
