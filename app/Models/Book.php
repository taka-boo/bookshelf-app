<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** 書籍のジャンルを取得する */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    /** 書籍のレビューを取得する */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
