<?php

namespace Database\Factories;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'target_date' => fake()->dateTimeBetween('+1 days', '+30 days')->format('Y-m-d'),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ];
    }
}