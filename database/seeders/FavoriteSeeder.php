<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $favorites = [
            0 => [1, 3, 5],       // 山田太郎
            1 => [2, 4, 6, 8],    // 鈴木花子
            2 => [1, 7, 9, 10, 11], // 田中一郎
            3 => [3, 5, 6],       // 佐藤美咲
            4 => [2, 4, 7, 9],    // 高橋健太
        ];

        foreach ($favorites as $userIndex => $bookIndexes) {
            $user = $users[$userIndex];
            $bookIds = $books->whereIn('id', $bookIndexes)->pluck('id')->toArray();
            $user->favoriteBooks()->syncWithoutDetaching($bookIds);

        }
    }
}
