<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** マイ読書レポートを表示する */
    public function index(): View
    {
        $user = auth()->user();
        $reviews = $user->reviews()->with('book.genres')->get();

        $stats = [
            'summary' => $this->buildSummary($user, $reviews),
            'rating_distribution' => $this->buildRatingDistribution($reviews),
            'top_rated_books' => $this->buildTopRatedBooks($reviews),
            'genre_ratings' => $this->buildGenreRatings($reviews),
        ];

        return view('reports.index', compact('stats'));
    }

    /** 基本サマリーを作成する */
    private function buildSummary($user, $reviews): array
    {
        return [
            'total_reviews' => $reviews->count(),
            'books_read' => $user->readingPlans()
                ->where('status', 'completed')
                ->count(),
            'average_rating' => round($reviews->avg('rating') ?? 0, 1),
        ];
    }

    /** 評価分布（★1〜★5の件数）を作成する */
    private function buildRatingDistribution($reviews): Collection
    {
        $grouped = $reviews->groupBy('rating');

        return collect(range(1, 5))
            ->map(fn ($rating) => $grouped->get($rating)?->count() ?? 0);
    }

    /** 高評価書籍TOP5を作成する（★4以上） */
    private function buildTopRatedBooks($reviews): array
    {
        return $reviews
            ->filter(fn ($review) => $review->rating >= 4)
            ->sortByDesc('rating')
            ->take(5)
            ->map(fn ($review) => [
                'id' => $review->book->id,
                'title' => $review->book->title,
                'author' => $review->book->author,
                'rating' => $review->rating,
            ])
            ->values()
            ->toArray();
    }

    /** ジャンル別評価傾向TOP5を作成する */
    private function buildGenreRatings($reviews): array
    {
        return $reviews
            ->flatMap(fn ($review) => $review->book->genres->map(
                fn ($genre) => [
                    'genre_id' => $genre->id,
                    'genre_name' => $genre->name,
                    'rating' => $review->rating,
                ]
            ))
            ->groupBy('genre_id')
            ->map(fn ($items) => [
                'id' => $items->first()['genre_id'],
                'name' => $items->first()['genre_name'],
                'count' => $items->count(),
                'average_rating' => round($items->avg('rating'), 1),
            ])
            ->sortByDesc('average_rating')
            ->take(5)
            ->values()
            ->toArray();
    }
}
