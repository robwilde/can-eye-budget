<?php

declare(strict_types=1);

test('can visit homepage', function () {
    $page = visit('/');
    
    $page->assertSee('Laravel');
});

test('can visit login page', function () {
    $page = visit('/login');
    
    $page->assertSee('Log in');
});