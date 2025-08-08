<?php

namespace WTE\App\WTE\WpDate;

use Carbon\Carbon;

Class DateFormatter
{
    protected $date;

    public function __construct(Carbon $date)
    {
        $this->date = $date;   
    }
    
    public function standardFormat()
    {
        return $this->date->format('Y-m-d');   
    }
    
    public function readable()
    {
        return "{$this->getDate()}  ({$this->getHour()} {$this->getAmOrPm()})";   
    }

    public function getDate()
    {
        (string) $isToday = $this->date->isToday()? '(Hoy)' : '';

        return "{$this->getDay()} {$this->date->format('j')} de {$this->getMonth()} de {$this->getYear()} {$isToday}";
    }

    public function getDay()
    {
        return static::$days[$this->date->dayOfWeek];   
    }

    public function getMonth()
    {
        return static::$months[$this->date->format('n') - 1];   
    }
    
    public function getYear()
    {
        return $this->date->format('Y');
    }

    public function getHour()
    {
        return $this->date->format('h');   
    }
    
    public function getAmOrPm()
    {
        return $this->date->format('H') > 12? 'PM' : 'AM';   
    }
    
    protected static $days = [
        'Domingo',
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sábado',
    ];

    protected static $months = [
        'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre',
    ];
}
