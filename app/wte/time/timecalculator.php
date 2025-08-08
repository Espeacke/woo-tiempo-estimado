<?php

namespace WTE\App\WTE\Time;

use Carbon\Carbon;
use WTE\Original\Collections\Collection;

class TimeCalculator
{
    protected $timeRange;
    protected $businessTime;

    public function __construct(TimeRange $timeRange, BusinessTime $businessTime)
    {
        $this->timeRange = $timeRange;
        $this->businessTime = $businessTime;
    }

    public function getCalculationFromDate(Carbon $date)
    {
        return new Collection([
            'min' => $this->getMinimunDateFromDate($date),
            'max' => $this->getMaximumDateFromDate($date)
        ]);        
    }

    public function getMinimunDateFromDate(Carbon $date)
    {
        return $this->getDateFromDate($date, 'min');
    }

    public function getMaximumDateFromDate(Carbon $date)
    {
        return $this->getDateFromDate($date, 'max');
    }
    
    /**
     * @param  string $type 'min'|'max'
     * @return Carbon       
     */
    public function getDateFromDate(Carbon $date, $type)
    {
        $calculatedDate = $date->copy();
        $workHours = $this->businessTime
                          ->getUnitAsHours([
                              'unit' => $this->timeRange->getUnit(),
                              'amount' => $this->timeRange->getRange()->get($type)
                          ]);

        if ($workHours > 0) {
            foreach (Collection::range(1, $workHours)->asArray() as $hour) {
                $calculatedDate = $this->findClosestBusinessDate($calculatedDate);
            } 
        }

        return $calculatedDate;
    }
 
    public function findClosestBusinessDate(Carbon $date)
    {
        $rightNowIsNotBusinessTime = !$this->businessTime->isBusinessDayAndHour([
            'hour' => $date->format('G'),
            'day' => $date->dayOfWeek,
        ]);

        if ($rightNowIsNotBusinessTime) {
            $date->addHours(1);

            return $this->findClosestBusinessDate($date);
        } else {
            do {
                $date->addHours(1);
            } while (!$this->businessTime->isBusinessDayAndHour([
                'hour' => $date->format('G'),
                'day' => $date->dayOfWeek,
            ]));

            return $date;
        }
    }

    public function getCurentPercentage(Carbon $startDate, Carbon $currentDate)
    {
        $totalHours = $this->businessTime->getUnitAsHours([
            'unit' => $this->timeRange->getUnit(),
            'amount' => $this->timeRange->getRange()->get('max')
        ]);

        $usedHoursSoFar = $this->getUsedHoursSoFar($startDate, $currentDate);

        return ($usedHoursSoFar * 100) / $totalHours;
    }
    
    protected function getUsedHoursSoFar(Carbon $startDate, Carbon $currentDate)
    {
        $totalHours = $this->businessTime->getUnitAsHours([
            'unit' => $this->timeRange->getUnit(),
            'amount' => $this->timeRange->getRange()->get('max')
        ]);
        $nextDate = $startDate;

        for ($usedHoursSoFar = 1; $usedHoursSoFar <= $totalHours; $usedHoursSoFar++) { 
            $nextDate = $this->findClosestBusinessDate($nextDate->copy());

            if ($nextDate->greaterThanOrEqualTo($currentDate)) {
                return $usedHoursSoFar;
            }
        }        

        return $totalHours;
    }
    
    public function getAddUnitMethodName()
    {
        return "add{$this->timeRange->getUnit()->upperCaseFirst()}"; 
    }
}
