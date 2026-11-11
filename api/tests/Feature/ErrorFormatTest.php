<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

it('renders unknown routes as a json 404 error', function () {
    $this->getJson('/api/v1/nope')
        ->assertNotFound()
        ->assertExactJson(['error' => ['code' => 'not_found', 'message' => 'Not found.']]);
});

it('renders wrong methods as a json 405 error', function () {
    $this->postJson('/api/v1/health')
        ->assertStatus(405)
        ->assertJsonPath('error.code', 'method_not_allowed');
});

it('renders validation failures with details', function () {
    Route::get('/api/v1/_test/validation', fn () => throw ValidationException::withMessages(['name' => ['Required.']]));

    $this->getJson('/api/v1/_test/validation')
        ->assertStatus(422)
        ->assertExactJson(['error' => [
            'code' => 'validation_failed',
            'message' => 'Required.',
            'details' => ['name' => ['Required.']],
        ]]);
});

it('hides server error messages when debug is off', function () {
    config(['app.debug' => false]);
    Route::get('/api/v1/_test/boom', fn () => throw new RuntimeException('secret'));

    $this->getJson('/api/v1/_test/boom')
        ->assertStatus(500)
        ->assertExactJson(['error' => ['code' => 'server_error', 'message' => 'Server error.']]);
});
