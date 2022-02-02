<?php

namespace App\Service;

use Exception;

class CalendarInterface
{
    private const MONTH_FRENCH = [
        'Janvier', 'Février', 'Mars',
        'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre',
        'Octobre', 'Novembre', 'Décembre'
    ];

    private const LAST_DAY_OF_WEEK = 6;

    public function makeCalendar(int $month, int $year): array
    {
        $weekTemplate = ['', '', '', '', '', '', ''];
        $week = 0;
        $nbOfDays = $this->numberOfDaysInMonth($month, $year);

        $calendar = [$weekTemplate];

        for ($i = 1; $i <= $nbOfDays; $i++) {
            $dayIndex = $this->getDayIndex($i, $month, $year);

            if ($i > 1 && $dayIndex === 0) {
                $week++;
                $calendar[] = $weekTemplate;
            }

            $calendar[$week][$dayIndex] = $i;
        }

        return $calendar;
    }

    public function getFrenchMonth(int $month): string
    {
        return self::MONTH_FRENCH[$month - 1];
    }

    private function numberOfDaysInMonth(int $month, int $year): int
    {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    private function getDayIndex(int $day, int $month, int $year): int
    {
        $dayPadded = sprintf("%02d", $day);

        $dayIndex = date('w', (int) strtotime("$year-$month-$dayPadded"));

        if ($dayIndex === '0') {
            return self::LAST_DAY_OF_WEEK;
        } else {
            return (int) $dayIndex - 1;
        }
    }
}
