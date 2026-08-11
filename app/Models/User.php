<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /** ユーザーが登録した書籍を取得する */
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    /** ユーザーが投稿したレビューを取得する */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /** ユーザーのお気に入りを取得する */
    public function favoriteBooks()
    {
        return $this->belongsToMany(Book::class, 'favorites');
    }

    /** ユーザーがいいねした書籍を取得する */
    public function likedReviews()
    {
        return $this->belongsToMany(Review::class, 'review_likes');
    }

    /** ユーザーの読書計画を取得する */
    public function readingPlans()
    {
        return $this->hasMany(ReadingPlan::class);
    }
}
