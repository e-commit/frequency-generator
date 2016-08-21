<?php

/**
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
    /**
     * Return the next date for "every day" frequency
     *
     * @param array $times Array of DateTime objects. Default: Only "00:00:00"
     * @return \DateTime
     */
    public function nextInEveryDay(array $times = array())
    {
        $times = $this->getDefaultHours($times);
        $frequencies = array();

        /** @var \DateTime $time */
        foreach ($times as $time) {
            $today = new \DateTime('now');
            $today->setTime($time->format('H'), $time->format('i'), $time->format('s'));
            $frequencies[] = $today;
            $tomorrow = new \DateTime('now +1 day');
            $tomorrow->setTime($time->format('H'), $time->format('i'), $time->format('s'));
            $frequencies[] = $tomorrow;
        }

        return $this->getNextFrequency($frequencies);
    }

    /**
     * Return the next date for "every week" frequency
     *
     * @param array $days Array of days in week (integers). (1=Monday => 7=Sunday). Default: Only "1" (monday)
     * @param array $times Array of DateTime objects. Default: Only "00:00:00"
     * @return \DateTime
     * @throws \Exception
     */
    public function nextInEveryWeek(array $days, array $times = array())
    {
        $availabledDays = array(
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        );

        $now = new \DateTime('now');
        $times = $this->getDefaultHours($times);
        $frequencies = array();

        foreach ($days as $day) {
            if (!is_integer($day) || $day < 1 || $day > 7) {
                throw new \Exception('Bad day '.$day);
            }

            $frequency = new \DateTime(sprintf('next %s', $availabledDays[$day]));
            $frequencies = array_merge($frequencies, $this->getDatesWithTimes($frequency, $times));

            if ($day == $now->format('N')) {
                $frequencies = array_merge($frequencies, $this->getDatesWithTimes($now, $times));
            }
        }

        return $this->getNextFrequency($frequencies);
    }

    /**
     * Return the next date for "every month" frequency
     *
     * @param array $days Array of days in month (integers). (1=>31). Default: Only "1" (1st)
     * @param array $times Array of DateTime objects. Default: Only "00:00:00"
     * @return \DateTime
     * @throws \Exception
     */
    public function nextInEveryMonth(array $days = array(1), array $times = array())
    {
        $times = $this->getDefaultHours($times);
        $frequencies = array();

        foreach ($days as $day) {
            if (!is_integer($day) || $day < 1 || $day > 31) {
                throw new \Exception('Bad day '.$day);
            }

            foreach ($times as $time) {
                array_push($frequencies, $this->searchNextDayWithTimeMonthly(date('Y'), date('m'), $day, $time, true));
            }
        }

        return $this->getNextFrequency($frequencies);
    }

    /**
     * Return the next date for "every quart" frequency
     *
     * @param array $monthOffsets Array of month offsets in quart (integers). (1 = January, April, July, October. 2 = February, May, August, November. 3 = March, June, September, December). Default: Only "1" (January, April, July, October)
     * @param array $daysInMonth Array of days in month (integers). (1=>31). Default: Only "1" (1st)
     * @param array $times Array of DateTime objects. Default: Only "00:00:00"
     * @return \DateTime
     */
    public function nextInEveryQuart(array $monthOffsets = array(1), array $daysInMonth = array(1), array $times = array())
    {
        $monthsByOffset = array(
            1 => array(1, 4, 7, 10),
            2 => array(2, 5, 8, 11),
            3 => array(3, 6, 9, 12),
        );

        return $this->nextByMonthOffset($monthOffsets, $daysInMonth, $times, $monthsByOffset);
    }

    /**
     * Return the next date for "every half year" frequency
     *
     * @param array $monthOffsets Array of month offsets in half year (integers). (1 = January, July. 2 = February, August. 3 = March, September. 4 = April, October. 5 = May , November. 6 = June, December). Default: Only "1" (January, July)
     * @param array $daysInMonth Array of days in month (integers). (1=>31). Default: Only "1" (1st)
     * @param array $times Array of DateTime objects. Default: Only "00:00:00"
     * @return \DateTime
     */
    public function nextInEveryHalfYear(array $monthOffsets  = array(1), array $daysInMonth = array(1), array $times = array())
    {
        $monthsByOffset = array(
            1 => array(1, 7),
            2 => array(2, 8),
            3 => array(3, 9),
            4 => array(4, 10),
            5 => array(5, 11),
            6 => array(6, 12),
        );

        return $this->nextByMonthOffset($monthOffsets, $daysInMonth, $times, $monthsByOffset);
    }

    /**
     * Return the next date for "every year" frequency
     *
     * @param array $monthOffsets Array of month offsets in year (integers). (1 = January => 12 => December). Default: Only "1" (January)
     * @param array $daysInMonth Array of days in month (integers). (1=>31). Default: Only "1" (1st)
     * @param array $times Array of DateTime objects. Default: Only "00:00:00"
     * @return \DateTime
     */
    public function nextInEveryYear(array $monthOffsets  = array(1), array $daysInMonth = array(1), array $times = array())
    {
        $monthsByOffset = array();
        foreach (range(1, 12) as $month) {
            $monthsByOffset[$month] = array($month);
        }

        return $this->nextByMonthOffset($monthOffsets, $daysInMonth, $times, $monthsByOffset);
    }

    protected function getDefaultHours(array $times = array())
    {
        if (0 === count($times)) {
            return array(new \DateTime('00:00:00'));
        }

        foreach ($times as $time) {
            if (!($time instanceof \DateTime)) {
                throw new \Exception('Times must be DateTime objects');
            }
        }

        return $times;
    }

    protected function getDatesWithTimes(\DateTime $date, array $times)
    {
        $dates = array();
        /** @var \DateTime $time */
        foreach ($times as $time) {
            $dateWithTime = clone $date;
            $dateWithTime->setTime($time->format('H'), $time->format('i'), $time->format('s'));
            $dates[] = $dateWithTime;
        }

        return $dates;
    }

    protected function getNextFrequency(array $frequencies)
    {
        $now = new \DateTime('now');
        $minFrequency = null;
        /** @var \DateTime $frequency */
        foreach ($frequencies as $frequency) {
            if ($frequency->getTimestamp() > $now->getTimestamp() && (null === $minFrequency || $frequency->getTimestamp() < $minFrequency->getTimestamp())) {
                $minFrequency = $frequency;
            }
        }

        if (null === $minFrequency) {
            return $now;
        }

        return $minFrequency;
    }

    protected function searchNextDayWithTimeMonthly($year, $month, $searchDay, \DateTime $time, $canChangeMonth)
    {
        $limit = new \DateTime('now');
        $monthFound = false;
        $month = \DateTime::createFromFormat('Y-m-d', \sprintf('%s-%s-1', $year, $month));

        //Search month
        while (!$monthFound) {
            $dayFound = false;
            $intDay = $searchDay;
            $intMonth = $month->format('m');
            $intYear = $month->format('Y');

            //Search day in test month
            while (!$dayFound) {
                if (\checkdate($intMonth, $intDay, $intYear)) {
                    //Day found in test month
                    $dayFound = true;
                } else {
                    //Day not found in test month
                    //Back 1 day
                    $intDay--;
                }
            }

            //Test day
            $testDate = \DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-%s', $intYear, $intMonth, $intDay));
            $testDate->setTime($time->format('H'), $time->format('i'), $time->format('s'));
            if ($testDate->getTimestamp() > $limit->getTimestamp()) {
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

    protected function nextByMonthOffset(array $monthOffsets, array $daysInMonth, array $times = array(), $monthsByOffset)
    {
        $times = $this->getDefaultHours($times);
        $frequencies = array();

        foreach ($monthOffsets as $monthOffset) {
            if (!is_integer($monthOffset) || $monthOffset < 1 || $monthOffset > count($monthsByOffset)) {
                throw new \Exception('Bad month offset'.$monthOffset);
            }

            foreach ($monthsByOffset[$monthOffset] as $month) {
                foreach ($daysInMonth as $dayInMonth) {
                    foreach ($times as $time) {
                        $dayFound = false;
                        $testMonth = \DateTime::createFromFormat('Y-m-d', \sprintf('%s-%s-1', date('Y'), $month));
                        while (!$dayFound) {
                            $frequency = $this->searchNextDayWithTimeMonthly($testMonth->format('Y'), $testMonth->format('m'), $dayInMonth, $time, false);
                            if ($frequency) {
                                $dayFound = true;
                                array_push($frequencies, $frequency);
                            } else {
                                $countMonthsByOffset = count($monthsByOffset);
                                for ($i = 1; $i <= $countMonthsByOffset; $i++) {
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
}
