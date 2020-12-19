<?php

declare(strict_types=1);

/*
 * This file is part of the frequency-generator package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\FrequencyGenerator;

class FrequencyGenerator
{
    protected $generateDateTimeImmutable = false;

    public function generateDateTimeImmutable(bool $generateDateTimeImmutable): self
    {
        $this->generateDateTimeImmutable = $generateDateTimeImmutable;

        return $this;
    }

    /**
     * Return the next date for "every day" frequency.
     *
     * @param array $times Array of DateTimeInterface objects. Default: Only "00:00:00"
     */
    public function nextInEveryDay(array $times = []): \DateTimeInterface
    {
        $times = $this->getDefaultTimes($times);
        $frequencies = [];

        /** @var \DateTimeInterface $time */
        foreach ($times as $time) {
            $today = $this->getNow();
            $today->setTime((int) $time->format('H'), (int) $time->format('i'), (int) $time->format('s'));
            $frequencies[] = $today;
            $tomorrow = $this->getNow()->modify('+1 day');
            $tomorrow->setTime((int) $time->format('H'), (int) $time->format('i'), (int) $time->format('s'));
            $frequencies[] = $tomorrow;
        }

        return $this->createResult($this->getNextFrequency($frequencies));
    }

    /**
     * Return the next date for "every week" frequency.
     *
     * @param array $days  Array of days in week (integers). (1=Monday => 7=Sunday). Default: Only "1" (monday)
     * @param array $times Array of DateTimeInterface objects. Default: Only "00:00:00"
     *
     * @throws \Exception
     */
    public function nextInEveryWeek(array $days = [1], array $times = []): \DateTimeInterface
    {
        $availabledDays = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];

        if (0 === \count($days)) {
            $days = [1];
        }

        $now = $this->getNow();
        $times = $this->getDefaultTimes($times);
        $frequencies = [];

        foreach ($days as $day) {
            if (!\is_int($day) || $day < 1 || $day > 7) {
                throw new \Exception('Bad day '.$day);
            }

            $frequency = $this->getNow();
            $frequency->modify(sprintf('next %s', $availabledDays[$day]));
            $frequencies = array_merge($frequencies, $this->getDatesWithTimes($frequency, $times));

            if ($day == $now->format('N')) {
                $frequencies = array_merge($frequencies, $this->getDatesWithTimes($now, $times));
            }
        }

        return $this->createResult($this->getNextFrequency($frequencies));
    }

    /**
     * Return the next date for "every month" frequency.
     *
     * @param array $days  Array of days in month (integers). (1=>31). Default: Only "1" (1st)
     * @param array $times Array of DateTimeInterface objects. Default: Only "00:00:00"
     *
     * @throws \Exception
     */
    public function nextInEveryMonth(array $days = [1], array $times = []): \DateTimeInterface
    {
        $now = $this->getNow();
        $times = $this->getDefaultTimes($times);
        $frequencies = [];

        if (0 === \count($days)) {
            $days = [1];
        }

        foreach ($days as $day) {
            if (!\is_int($day) || $day < 1 || $day > 31) {
                throw new \Exception('Bad day '.$day);
            }

            foreach ($times as $time) {
                $frequencies[] = $this->searchNextDayWithTimeMonthly((int) $now->format('Y'), (int) $now->format('m'), $day, $time, true);
            }
        }

        return $this->createResult($this->getNextFrequency($frequencies));
    }

    /**
     * Return the next date for "every quart" frequency.
     *
     * @param array $monthOffsets Array of month offsets in quart (integers). (1 = January, April, July, October. 2 = February, May, August, November. 3 = March, June, September, December). Default: Only "1" (January, April, July, October)
     * @param array $daysInMonth  Array of days in month (integers). (1=>31). Default: Only "1" (1st)
     * @param array $times        Array of DateTimeInterface objects. Default: Only "00:00:00"
     */
    public function nextInEveryQuart(array $monthOffsets = [1], array $daysInMonth = [1], array $times = []): \DateTimeInterface
    {
        if (0 === \count($monthOffsets)) {
            $monthOffsets = [1];
        }

        if (0 === \count($daysInMonth)) {
            $daysInMonth = [1];
        }

        $monthsByOffset = [
            1 => [1, 4, 7, 10],
            2 => [2, 5, 8, 11],
            3 => [3, 6, 9, 12],
        ];

        return $this->createResult($this->nextByMonthOffset($monthOffsets, $daysInMonth, $times, $monthsByOffset));
    }

    /**
     * Return the next date for "every half year" frequency.
     *
     * @param array $monthOffsets Array of month offsets in half year (integers). (1 = January, July. 2 = February, August. 3 = March, September. 4 = April, October. 5 = May , November. 6 = June, December). Default: Only "1" (January, July)
     * @param array $daysInMonth  Array of days in month (integers). (1=>31). Default: Only "1" (1st)
     * @param array $times        Array of DateTimeInterface objects. Default: Only "00:00:00"
     */
    public function nextInEveryHalfYear(array $monthOffsets = [1], array $daysInMonth = [1], array $times = []): \DateTimeInterface
    {
        if (0 === \count($monthOffsets)) {
            $monthOffsets = [1];
        }

        if (0 === \count($daysInMonth)) {
            $daysInMonth = [1];
        }

        $monthsByOffset = [
            1 => [1, 7],
            2 => [2, 8],
            3 => [3, 9],
            4 => [4, 10],
            5 => [5, 11],
            6 => [6, 12],
        ];

        return $this->createResult($this->nextByMonthOffset($monthOffsets, $daysInMonth, $times, $monthsByOffset));
    }

    /**
     * Return the next date for "every year" frequency.
     *
     * @param array $monthOffsets Array of month offsets in year (integers). (1 = January => 12 => December). Default: Only "1" (January)
     * @param array $daysInMonth  Array of days in month (integers). (1=>31). Default: Only "1" (1st)
     * @param array $times        Array of DateTimeInterface objects. Default: Only "00:00:00"
     */
    public function nextInEveryYear(array $monthOffsets = [1], array $daysInMonth = [1], array $times = []): \DateTimeInterface
    {
        if (0 === \count($monthOffsets)) {
            $monthOffsets = [1];
        }

        if (0 === \count($daysInMonth)) {
            $daysInMonth = [1];
        }

        $monthsByOffset = [];
        foreach (range(1, 12) as $month) {
            $monthsByOffset[$month] = [$month];
        }

        return $this->createResult($this->nextByMonthOffset($monthOffsets, $daysInMonth, $times, $monthsByOffset));
    }

    final protected function getDefaultTimes(array $times = []): array
    {
        if (0 === \count($times)) {
            return [new \DateTime('00:00:00')];
        }

        foreach ($times as $time) {
            if (!($time instanceof \DateTimeInterface)) {
                throw new \Exception('Times must be DateTimeInterface objects');
            }
        }

        return $times;
    }

    final protected function getDatesWithTimes(\DateTime $date, array $times): array
    {
        $dates = [];
        /** @var \DateTimeInterface $time */
        foreach ($times as $time) {
            $dateWithTime = clone $date;
            $dateWithTime->setTime((int) $time->format('H'), (int) $time->format('i'), (int) $time->format('s'));
            $dates[] = $dateWithTime;
        }

        return $dates;
    }

    final protected function getNextFrequency(array $frequencies): \DateTime
    {
        $now = $this->getNow();
        $minFrequency = null;
        /** @var \DateTime $frequency */
        foreach ($frequencies as $frequency) {
            if ($frequency > $now && (null === $minFrequency || $frequency < $minFrequency)) {
                $minFrequency = $frequency;
            }
        }

        if (null === $minFrequency) {
            return $now;
        }

        return $minFrequency;
    }

    final protected function searchNextDayWithTimeMonthly(int $year, int $month, int $searchDay, \DateTimeInterface $time, bool $canChangeMonth): ?\DateTime
    {
        $limit = $this->getNow();
        $monthFound = false;
        $month = \DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-1', $year, $month));

        //Search month
        while (!$monthFound) {
            $dayFound = false;
            $intDay = (int) $searchDay;
            $intMonth = (int) $month->format('m');
            $intYear = (int) $month->format('Y');

            //Search day in test month
            while (!$dayFound) {
                if (checkdate($intMonth, $intDay, $intYear)) {
                    //Day found in test month
                    $dayFound = true;
                } else {
                    //Day not found in test month
                    //Back 1 day
                    --$intDay;
                }
            }

            //Test day
            $testDate = \DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-%s', $intYear, $intMonth, $intDay));
            $testDate->setTime((int) $time->format('H'), (int) $time->format('i'), (int) $time->format('s'));
            if ($testDate > $limit) {
                //Date is ok
                $monthFound = true;
            } else {
                //Date is not ok. Test with text month or return null
                if ($canChangeMonth) {
                    $month->modify('first day of next month');
                } else {
                    return null;
                }
            }
        }

        return $testDate;
    }

    final protected function nextByMonthOffset(array $monthOffsets, array $daysInMonth, array $times, array $monthsByOffset): \DateTime
    {
        $now = $this->getNow();
        $times = $this->getDefaultTimes($times);
        $frequencies = [];

        foreach ($monthOffsets as $monthOffset) {
            if (!\is_int($monthOffset) || $monthOffset < 1 || $monthOffset > \count($monthsByOffset)) {
                throw new \Exception('Bad month offset '.$monthOffset);
            }

            foreach ($monthsByOffset[$monthOffset] as $month) {
                foreach ($daysInMonth as $dayInMonth) {
                    if (!\is_int($dayInMonth) || $dayInMonth < 1 || $dayInMonth > 31) {
                        throw new \Exception('Bad day '.$dayInMonth);
                    }
                    foreach ($times as $time) {
                        $dayFound = false;
                        $testMonth = \DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-1', $now->format('Y'), $month));
                        while (!$dayFound) {
                            $frequency = $this->searchNextDayWithTimeMonthly((int) $testMonth->format('Y'), (int) $testMonth->format('m'), $dayInMonth, $time, false);
                            if ($frequency) {
                                $dayFound = true;
                                $frequencies[] = $frequency;
                            } else {
                                $countMonthsByOffset = \count($monthsByOffset);
                                for ($i = 1; $i <= $countMonthsByOffset; ++$i) {
                                    $testMonth->modify('first day of next month');
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->getNextFrequency($frequencies);
    }

    final protected function createResult(\DateTime $date): \DateTimeInterface
    {
        if (true === $this->generateDateTimeImmutable) {
            return \DateTimeImmutable::createFromMutable($date);
        }

        return $date;
    }

    protected function getNow(): \DateTime
    {
        return new \DateTime('now');
    }
}
