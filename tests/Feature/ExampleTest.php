<?php

declare(strict_types=1);

test('home redirects unauthenticated users to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
