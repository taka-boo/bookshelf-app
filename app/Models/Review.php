<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** レビューされた書籍を取得する */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /** レビューをいいねしたユーザーを取得する */
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'review_likes');
    }
}
