<?php

it('reports ok when database and cache answer', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertExactJson([
            'status' => 'ok',
            'checks' => ['database' => 'ok', 'cache' => 'ok'],
        ]);
});
