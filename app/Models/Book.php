<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
        'user_id',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    /** 書籍の登録者を取得する */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** 書籍のジャンルを取得する */
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    /** 書籍のレビューを取得する */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
