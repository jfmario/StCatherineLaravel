<?php

namespace App\Support;

use App\Enums\LiturgicalColor;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Determines the Antiochian liturgical color for a moment in time.
 *
 * Movable feasts follow the Eastern Orthodox (Julian-calendar) Pascha, and
 * 6 PM US Eastern or later counts as the following day.
 */
class LiturgicalCalendar
{
    public const TIMEZONE = 'America/New_York';

    private const DAY_ROLLOVER_HOUR = 18;

    private const OPTIONAL_COLOR = LiturgicalColor::Gold;

    /**
     * Non-optional fixed-date ranges as [startMonthDay, endMonthDay, color],
     * where month-days are written as MMDD. Dates not listed are optional.
     *
     * @var list<array{0: int, 1: int, 2: LiturgicalColor}>
     */
    private const FIXED_RANGES = [
        [202, 209, LiturgicalColor::Blue],
        [325, 325, LiturgicalColor::Blue],
        [629, 630, LiturgicalColor::Red],
        [701, 731, LiturgicalColor::Green],
        [801, 805, LiturgicalColor::Blue],
        [807, 812, LiturgicalColor::Blue],
        [814, 823, LiturgicalColor::Blue],
        [829, 829, LiturgicalColor::Red],
        [908, 912, LiturgicalColor::Blue],
        [914, 921, LiturgicalColor::Red],
        [1115, 1120, LiturgicalColor::Red],
        [1121, 1125, LiturgicalColor::Blue],
        [1126, 1224, LiturgicalColor::Red],
    ];

    /**
     * Gold feast days that interrupt otherwise colored ranges.
     *
     * @var list<int>
     */
    private const FIXED_GOLD_DAYS = [806, 813, 901, 913];

    /**
     * The Orthodox Pascha as a Gregorian calendar date (midnight UTC).
     */
    public function orthodoxPascha(int $year): CarbonImmutable
    {
        $a = $year % 4;
        $b = $year % 7;
        $c = $year % 19;
        $d = (19 * $c + 15) % 30;
        $e = (2 * $a + 4 * $b - $d + 34) % 7;
        $sum = $d + $e + 114;

        $julianToGregorianOffset = intdiv($year, 100) - intdiv($year, 400) - 2;

        return CarbonImmutable::create($year, intdiv($sum, 31), $sum % 31 + 1, 0, 0, 0, 'UTC')
            ->addDays($julianToGregorianOffset);
    }

    public function colorAt(CarbonInterface $moment): LiturgicalColor
    {
        $eastern = CarbonImmutable::instance($moment)->setTimezone(self::TIMEZONE);
        $isEvening = $eastern->hour >= self::DAY_ROLLOVER_HOUR;

        $liturgicalDay = $this->utcDate($isEvening ? $eastern->addDay() : $eastern);

        if ($isEvening && $this->daysFromPascha($this->utcDate($eastern)) === -16) {
            return LiturgicalColor::Blue;
        }

        $monthDay = $liturgicalDay->month * 100 + $liturgicalDay->day;

        if ($monthDay === 325) {
            return LiturgicalColor::Blue;
        }

        return $this->movableColor($liturgicalDay, $monthDay) ?? $this->fixedColor($monthDay);
    }

    private function movableColor(CarbonImmutable $day, int $monthDay): ?LiturgicalColor
    {
        $offset = $this->daysFromPascha($day);

        return match (true) {
            $offset >= -28 && $offset <= -23 => LiturgicalColor::Red,
            $offset >= -48 && $offset <= -9 => LiturgicalColor::Purple,
            $offset >= -8 && $offset <= -7 => LiturgicalColor::Gold,
            $offset >= -6 && $offset <= -4 => LiturgicalColor::Purple,
            $offset === -3 => LiturgicalColor::Red,
            $offset === -2 => LiturgicalColor::Purple,
            $offset >= -1 && $offset <= 48 => LiturgicalColor::White,
            $offset >= 49 && $monthDay <= 628 => LiturgicalColor::Green,
            default => null,
        };
    }

    private function fixedColor(int $monthDay): LiturgicalColor
    {
        if (in_array($monthDay, self::FIXED_GOLD_DAYS, true)) {
            return LiturgicalColor::Gold;
        }

        foreach (self::FIXED_RANGES as [$start, $end, $color]) {
            if ($monthDay >= $start && $monthDay <= $end) {
                return $color;
            }
        }

        return self::OPTIONAL_COLOR;
    }

    private function daysFromPascha(CarbonImmutable $day): int
    {
        return (int) $this->orthodoxPascha($day->year)->diffInDays($day, false);
    }

    private function utcDate(CarbonInterface $date): CarbonImmutable
    {
        return CarbonImmutable::create($date->year, $date->month, $date->day, 0, 0, 0, 'UTC');
    }
}
