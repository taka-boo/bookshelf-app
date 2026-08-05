<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function プロフィール情報を更新できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/user/profile-information', [
            'name' => '更新後の名前',
            'email' => 'updated@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '更新後の名前',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function 名前が空だとプロフィールを更新できない()
    {
        $user = User::factory()->create(['name' => '元の名前']);

        $response = $this->actingAs($user)->put('/user/profile-information', [
            'name' => '',
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors(['name'], null, 'updateProfileInformation');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '元の名前',
        ]);
    }

    /** @test */
    public function パスワードを更新できる()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user)->put('/user/password', [
            'current_password' => 'old-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
    }

    /** @test */
    public function 現在のパスワードが違うと更新できない()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/user/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertSessionHasErrors(['current_password'], null, 'updatePassword');
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
