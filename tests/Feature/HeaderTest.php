<?php

test('interacting with headers', function () {
    $response = $this->withHeaders([
        'X-Header' => 'Pranta',
    ])->post('/user', ['name' => 'Mazumder']);

    $response->assertStatus(201);
});
