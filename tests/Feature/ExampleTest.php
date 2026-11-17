<?php

use Carbon\CarbonImmutable;

test('home page shows parish content', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Welcome to Our Parish')
        ->assertSee('Divine Liturgy: Sun 4:00 PM');
});

test('home page has no liturgical color or appearance controls', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Liturgical Color')
        ->assertDontSee('aria-label="Appearance"', false);
});

test('home page applies the liturgical color for the current date in Eastern time', function (string $easternTime, string $color) {
    $this->travelTo(CarbonImmutable::parse($easternTime, 'America/New_York'));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-liturgical="'.$color.'"', false);
})->with([
    ['2026-11-20 12:00', 'red'],
    ['2026-11-14 18:00', 'red'],
    ['2026-11-14 12:00', 'gold'],
]);

test('home page does not link to login or other app pages', function () {
    $this->get(route('home'))
        ->assertDontSee('/login', false)
        ->assertDontSee('/register', false)
        ->assertDontSee('/dashboard', false);
});

test('login page lives at /login', function () {
    expect(route('login', absolute: false))->toBe('/login');

    $this->get('/login')->assertOk();
});

test('login page applies the liturgical color for the current date in Eastern time', function (string $easternTime, string $color) {
    $this->travelTo(CarbonImmutable::parse($easternTime, 'America/New_York'));

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('data-liturgical="'.$color.'"', false);
})->with([
    ['2026-11-20 12:00', 'red'],
    ['2026-11-14 18:00', 'red'],
    ['2026-11-14 12:00', 'gold'],
]);
