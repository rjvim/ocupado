<?php

namespace Betalectic\Ocupado;

use Betalectic\Ocupado\Models\Entity;

class AvailabilityFilter {

	
	public $entity;

	public $date = false;

	public $ignoreBlockedDates = false;

	public $interval = 60;
	public $slotSize = false;

	public $extrapolateFrom = false;
	public $extrapolateTo = false;

	public $ignoreFrom = false;
	public $ignoreTo = false;

	public function __construct($entity)
	{
		$this->entity = Entity::firstOrCreate([
			'type' => get_class($entity),
			'value' => $entity->getKey()
		]);
	}

	// public function interval($value)
	// {
	// 	$this->interval = $value;
	// }


    public function __call($name, $arguments)
    {

    	switch ($name) {

    		case 'date':
    			$this->date = $arguments[0];
    			break;

    		case 'interval':
    			$this->interval = $arguments[0];
    			break;

    		case 'ignoreBlockedDates':
    			$this->ignoreBlockedDates = $arguments[0];
    			break;

    		case 'slotSize':
				$this->slotSize = $arguments[0];
				break;

    		case 'extrapolate':
				$this->extrapolateFrom = $arguments[0];
				$this->extrapolateTo = $arguments[1];
				break;

    		case 'ignore':
				$this->ignoreFrom = $arguments[0];
				$this->ignoreTo = $arguments[1];
				break;

    		default:
    			# code...
    			break;
    	}

    	return $this;
    }



}