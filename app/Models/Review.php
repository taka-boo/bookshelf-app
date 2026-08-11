<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];

    /** レビューの投稿者を取得する */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** レビューされた書籍を取得する */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** レビューをいいねしたユーザーを取得する */
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_likes');
    }
}
