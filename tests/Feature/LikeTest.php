<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function レビューにいいねできる()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function もう一度押すといいねが解除される()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();
        $review->likedByUsers()->attach($user->id);

        $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function 未ログインではいいね操作ができない()
    {
        $review = Review::factory()->create();

        $response = $this->post("/reviews/{$review->id}/like");

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('review_likes', 0);
    }
}
