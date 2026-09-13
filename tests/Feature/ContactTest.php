<?php

test('return the contact page', function () {
    $response = $this->get('/contact');
    $response->assertStatus(200);
});
