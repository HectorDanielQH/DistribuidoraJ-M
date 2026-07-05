<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users can log in with their username', function () {
    $user = User::factory()->create([
        'username' => 'vendedor.demo',
        'password' => bcrypt('secreto123'),
    ]);

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'secreto123',
    ]);

    $response->assertRedirect('/home');
    $this->assertAuthenticatedAs($user);
});
