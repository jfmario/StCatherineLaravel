<?php

use App\Enums\LiturgicalColor;
use App\Support\LiturgicalCalendar;
use Carbon\CarbonImmutable;

test('it computes the Orthodox Pascha', function (int $year, string $expected) {
    expect((new LiturgicalCalendar)->orthodoxPascha($year)->toDateString())->toBe($expected);
})->with([
    [2024, '2024-05-05'],
    [2025, '2025-04-20'],
    [2026, '2026-04-12'],
    [2027, '2027-05-02'],
    [2030, '2030-04-28'],
]);

test('it picks the liturgical color for an Eastern time', function (string $easternTime, LiturgicalColor $expected) {
    $moment = CarbonImmutable::parse($easternTime, 'America/New_York');

    expect((new LiturgicalCalendar)->colorAt($moment))->toBe($expected);
})->with([
    // 2026: Pascha is April 12.
    'optional day' => ['2026-11-14 12:00', LiturgicalColor::Gold],
    'rolls to next day at 6 PM' => ['2026-11-14 18:00', LiturgicalColor::Red],
    'just before 6 PM' => ['2026-11-14 17:59', LiturgicalColor::Gold],
    'nativity of theotokos' => ['2026-09-08 09:00', LiturgicalColor::Blue],
    'dedication of resurrection' => ['2026-09-13 09:00', LiturgicalColor::Gold],
    'presentation' => ['2026-11-22 09:00', LiturgicalColor::Blue],
    'christmas' => ['2026-12-25 09:00', LiturgicalColor::Gold],
    'new year eve evening' => ['2026-12-31 20:00', LiturgicalColor::Gold],
    'meeting of the lord' => ['2026-02-05 09:00', LiturgicalColor::Blue],
    'cheesefare sunday daytime' => ['2026-02-22 12:00', LiturgicalColor::Gold],
    'forgiveness vespers' => ['2026-02-22 18:00', LiturgicalColor::Purple],
    'sunday of the cross' => ['2026-03-15 12:00', LiturgicalColor::Red],
    'friday of cross week' => ['2026-03-20 12:00', LiturgicalColor::Red],
    'saturday after cross week' => ['2026-03-21 12:00', LiturgicalColor::Purple],
    'annunciation in lent' => ['2026-03-25 12:00', LiturgicalColor::Blue],
    'akathist friday evening' => ['2026-03-27 18:00', LiturgicalColor::Blue],
    'akathist saturday morning' => ['2026-03-28 09:00', LiturgicalColor::Purple],
    'lazarus saturday' => ['2026-04-04 09:00', LiturgicalColor::Gold],
    'palm sunday daytime' => ['2026-04-05 12:00', LiturgicalColor::Gold],
    'palm sunday evening' => ['2026-04-05 18:00', LiturgicalColor::Purple],
    'great thursday morning' => ['2026-04-09 09:00', LiturgicalColor::Red],
    'great friday' => ['2026-04-10 12:00', LiturgicalColor::Purple],
    'great friday evening' => ['2026-04-10 18:00', LiturgicalColor::White],
    'pascha' => ['2026-04-12 12:00', LiturgicalColor::White],
    'saturday before pentecost' => ['2026-05-30 12:00', LiturgicalColor::White],
    'pentecost' => ['2026-05-31 12:00', LiturgicalColor::Green],
    'june 28' => ['2026-06-28 12:00', LiturgicalColor::Green],
    'june 29' => ['2026-06-29 12:00', LiturgicalColor::Red],
    'july' => ['2026-07-15 12:00', LiturgicalColor::Green],
    'transfiguration' => ['2026-08-06 12:00', LiturgicalColor::Gold],
    'dormition' => ['2026-08-15 12:00', LiturgicalColor::Blue],
    'beheading of the forerunner' => ['2026-08-29 12:00', LiturgicalColor::Red],
]);

test('it converts other timezones to Eastern time', function () {
    $utcMoment = CarbonImmutable::parse('2026-11-14 23:30', 'UTC');

    expect((new LiturgicalCalendar)->colorAt($utcMoment))->toBe(LiturgicalColor::Red);
});
