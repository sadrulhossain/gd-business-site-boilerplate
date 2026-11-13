<?php

use Illuminate\Support\Facades\Hash;

it('requires authentication', function () {
    $this->patchJson('/api/v1/me', ['name' => 'x'])->assertUnauthorized();
});

it('renames the user and answers like GET /me', function () {
    $user = actingAsRole('nobody');

    $this->patchJson('/api/v1/me', ['name' => 'New Name'])
        ->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.email', $user->email);
});

it('rejects an empty name', function () {
    actingAsRole('nobody');

    $this->patchJson('/api/v1/me', ['name' => ''])->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['name']]]);
});

it('changes the password only with the current one, confirmed and long enough', function () {
    $user = actingAsRole('nobody'); // factory password is "password"

    $this->patchJson('/api/v1/me', ['password' => 'a-brand-new-password'])
        ->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['current_password']]]);
    $this->patchJson('/api/v1/me', ['current_password' => 'wrong', 'password' => 'a-brand-new-password', 'password_confirmation' => 'a-brand-new-password'])
        ->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['current_password']]]);
    $this->patchJson('/api/v1/me', ['current_password' => 'password', 'password' => 'short', 'password_confirmation' => 'short'])
        ->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['password']]]);
    $this->patchJson('/api/v1/me', ['current_password' => 'password', 'password' => 'a-brand-new-password', 'password_confirmation' => 'different-one-here'])
        ->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['password']]]);

    $this->patchJson('/api/v1/me', ['current_password' => 'password', 'password' => 'a-brand-new-password', 'password_confirmation' => 'a-brand-new-password'])->assertOk();

    expect(Hash::check('a-brand-new-password', $user->refresh()->password))->toBeTrue();
});
