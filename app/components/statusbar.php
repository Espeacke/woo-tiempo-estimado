<?php

namespace WTE\App\Components;

use Carbon\Carbon;
use WTE\App\WTE\Time\BusinessTime;
use WTE\App\WTE\Time\TimeCalculator;
use WTE\Original\Collections\Collection;
use WTE\Original\Presentation\Component;

Class StatusBar extends Component
{
    protected $file = 'statusbar.php';
    protected $isTheFirstStep = true;
    protected $firstStepInitialPercentage = 2;
	
    protected $timeCalculator;
    protected $calculatedDate;
    protected $orderDate;

    public function __construct(TimeCalculator $timeCalculator, Collection $calculatedDate, Carbon $orderDate)
    {
        $this->timeCalculator = $timeCalculator;   
        $this->calculatedDate = $calculatedDate;

        $this->orderDate = $orderDate;
    }
    

    public function getSteps()
    {
        (array) $steps = new Collection([]);

        foreach($this->getDatabaseSteps() as $databaseStep) {
            if ($this->isTheFirstStep) {
                $this->isTheFirstStep = false;
                $databaseStep->compoundPercentage = $this->firstStepInitialPercentage;
            } else {
                $databaseStep->compoundPercentage = (string) $steps->reduce(function($globalValue, $step) {
                    return $globalValue? $globalValue + $step->percentage : $step->percentage;
                });
            }

            $steps->push(
                $databaseStep
            );
        }

        /*number*/ $currentPercentage = $this->timeCalculator->getCurentPercentage(
            $startDate   = $this->orderDate, 
            $currentDate = Carbon::now()
        );

        (object) $completedSteps = $steps->filter($this->getPercentageFilter('lower', $currentPercentage));

        (object) $uncompletedSteps = $steps->filter($this->getPercentageFilter('higher', $currentPercentage));

        $uncompletedSteps = $uncompletedSteps->sort(function($first, $second){
            return $first->compoundPercentage < $second->compoundPercentage? -1 : 1;
        });

        $activeStep = $uncompletedSteps->shift();

        $completedSteps->forEvery($this->getSetState('completed'));
        $uncompletedSteps->forEvery($this->getSetState('pending'));

        call_user_func($this->getSetState('active'), $activeStep);

        return $steps;
    }

    protected function getSetState($state)
    {
        return function($step) use($state) {$step->state = $state;};
    }
    

    protected function getPercentageFilter($operator, $currentPercentage)
    {
        return function($step) use ($currentPercentage, $operator) {
            if ($operator === 'lower') {
                return $step->compoundPercentage < $currentPercentage;            
            }

            return $step->compoundPercentage >= $currentPercentage;
        };   
    }
    

    protected function getStepState($databaseStep, Collection $steps)
    {
        if ($databaseStep->compoundPercentage > $currentPercentage) {
            return 'completed';
        } elseif ($databaseStep->compoundPercentage ) {
            # code...
        }
        /*if (a past step is active then this is pending) {

        }*/
    }
    

    protected function getDatabaseSteps()
    {
        $items = [];

        try {
            (object) $data = json_decode(get_option('wte_progress_items', '{"items": []}'));

            $items = $data->items;
        } catch (\Exception $e) {
            $items = [];
        }

        return array_merge($items, [(object) []]);
        /*[
            (object) [
                'label' => 'preparando su pedido',
                'description' => 'estamos preparando su pedido con la operadora o marca emisora-> Su ppago se encuentra acrteditado, no necesita hacer mas.',
                'percentage' => '10'
            ],
            (object) [
                'label' => 'El IMEI proprcionado por el cliente ha entrado en proceso',
                'description' => 'estamos preparando su pedido con la ppago se encuentra acrteditado, no necesita hacer mas. operadora o marca emisora-> Su ppago se encuentra acrteditado, no necesita hacer mas.',
                'percentage' => '22.5'
            ],
            (object) [
                'label' => 'EL pedido se encuentra en tramite de lieracion/activacion',
                'description' => 'estamos preparando su pedido con la operadora o marca emisora-> Su ppago se encuentra acrteditado, no necesita hacer mas.',
                'percentage' => '22.5'
            ],
            (object) [
                'label' => 'Su pedido sigue por buen camino',
                'description' => 'estamos preparando su pedido con la operadora o marca emisora-> Su ppago se encuentra acrteditado, no necesita hacer mas, preparando su pedido',
                'percentage' => '22.5'
            ],
            (object) [
                'label' => 'Su pedido esta en proceso final y esta programado para su entrega lo mas pronto',
                'description' => 'estamos preparando su pedido con la operadora o marca emisora-> Su ppago se encuentra.',
                'percentage' => '22.5'
            ],
        ];   */
    }
    
 
    public function getIconMarkup()
    {
        return '
<?xml version="1.0" encoding="iso-8859-1"?>
<!-- Generator: Adobe Illustrator 16.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
     width="611.99px" height="611.99px" viewBox="0 0 611.99 611.99" style="enable-background:new 0 0 611.99 611.99;"
     xml:space="preserve">
<g>
    <g id="_x39__34_">
        <g>
            <path d="M589.105,80.63c-30.513-31.125-79.965-31.125-110.478,0L202.422,362.344l-69.061-70.438
                c-30.513-31.125-79.965-31.125-110.478,0c-30.513,31.125-30.513,81.572,0,112.678l124.29,126.776
                c30.513,31.125,79.965,31.125,110.478,0l331.453-338.033C619.619,162.202,619.619,111.755,589.105,80.63z"/>
        </g>
    </g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
</svg>
';
    }
          
}