<?php

namespace WTE\App\WTE\Time;

use WTE\Original\Collections\Collection;

class BusinessTime
{
    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;

    protected $hours;
    protected $days;

    public function __construct(Collection $hours, Collection $days)
    {
        $this->hours = $hours;
        $this->days = $days;
    }

    public function getUnitAsHours(array $time)
    {
        (object) $unit = $time['unit'];
        (object) $amount = $time['amount'];

        if ($unit->is('hours')) return $amount;

        return $this->getDaysAsHours($amount);
    }

    public function isBusinessDay($weekDayAsInteger)
    {
        return $this->days->contain($weekDayAsInteger);   
    }
    
    public function isBusinessHour($hourFrom0to24)
    {
        return $this->hours->contain($hourFrom0to24);   
    }

    public function isBusinessDayAndHour(array $DayAndHour)
    {
        (integer) $weekDayAsInteger = $DayAndHour['day'];
        (integer) $hourFrom0to24 = $DayAndHour['hour'];

        return $this->isBusinessDay($weekDayAsInteger) && 
               $this->isBusinessHour($hourFrom0to24);   
    }

    public function getDaysAsHours($totalNumberOfdays)
    {
        $numberOfHours = ($totalNumberOfdays * $this->getTotalBusinessHours()) / 1;

        return (integer) (is_float($numberOfHours) ? ceil($numberOfHours) : $numberOfHours);
    }
    
    public function getHoursAsDays($totalNumberOfHours)
    {
        $numberOfDays = ($totalNumberOfHours * 1) / $this->getTotalBusinessHours();

        if (is_float($numberOfDays) && $numberOfDays >= 0) {
            return (float) number_format($numberOfDays, 1);
        }

        return (integer) $numberOfDays;
    }

    public function getIncompleteDaysAsHours($daysWithDecimals)
    {
        (float) $fractionOfDay = ($daysWithDecimals - floor($daysWithDecimals));
        (integer) $fractionAsInteger = (integer) explode('.', number_format($fractionOfDay, $decimals = 1))[1];

        return (integer) ((($fractionAsInteger * $this->getTotalBusinessHours()) / 10) / 1);   
    }

    public function getTotalBusinessHours()
    {
        return $this->hours->filter('is_numeric')->count();
    }
}
