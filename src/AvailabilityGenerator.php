<?php

namespace Betalectic\Ocupado;

use Illuminate\Support\Facades\Storage;
use Betalectic\Ocupado\Models\Entity;
use Betalectic\Ocupado\Models\Availability;
use Betalectic\Ocupado\Models\Event;
use Betalectic\Ocupado\Models\BlockedTiming;
use Betalectic\Ocupado\Helpers;
use Carbon\Carbon;

class AvailabilityGenerator {

	public $filter;
	public $date;	
	public $entity;

	public $interval = 60;
	public $slotSize = false;

	public $timings = [];
	public $availabilities = [];

	public function __construct($filter)
	{
		$this->filter = $filter;

		$this->entity = $filter->entity;

		if(!$filter->date)
		{
			$this->timings = Availability::where([
				'entity_id' => $this->entity->id
			])
			->orderBy('start_time','asc')
			->get();

			return true;
		}

		$this->date = $filter->date;
		$this->interval = $filter->interval;
		$this->slotSize = $filter->slotSize;

		if($filter->ignoreFrom && $filter->ignoreTo)
		{
			$this->ignoreFrom = $filter->ignoreFrom;
			$this->ignoreTo = $filter->ignoreTo;
		}

		$date = Carbon::parse($filter->date);
		$dayOfWeek = strtolower($date->englishDayOfWeek);

		$this->availabilities = Availability::where([
			'entity_id' => $this->entity->id,
			'day_of_week' => $dayOfWeek
		])->orderBy('start_time','asc')->get();

		if($filter->extrapolateFrom && $filter->extrapolateTo)
		{
			$this->extrapolate($filter->extrapolateFrom, $filter->extrapolateTo);
		}

		$this->findAvailableTimings();

		if(!$filter->ignoreBlockedDates)
		{
			$this->markBlockedDates();
		}

		$this->checkPastTimes();

		$this->sortTimings();

		$this->markBusyTimings();

		if($filter->slotSize)
		{
			$this->generateSlots($filter->slotSize);
		}

		if($filter->ignoreFrom && $filter->ignoreTo)
		{
			$this->markIgnoredTimings($filter->ignoreFrom, $filter->ignoreTo);
		}
	}

	public function extrapolate($from, $to)
	{
		$firstTime = Carbon::createFromTimestamp(strtotime($this->availabilities->first()->start_time));

		$from = explode(':',$from)[0].':'.$firstTime->format('i');
		$to = explode(':',$to)[0].':'.$firstTime->format('i');

		$startTime = Carbon::createFromTimestamp(strtotime($from));
		$endTime = Carbon::createFromTimestamp(strtotime($to));

		$timings = [];

		while($startTime->diffInMinutes($endTime, false) >= 0)
		{
			$timings[] = ['time' => $startTime->format('H:i:s'), 'available' => false];
			$startTime->addMinutes($this->interval);
		}

		$this->timings = $timings;

	}

	function searchForId($find, $value, $array) 
	{
	   foreach ($array as $index => $item) {
	       if ($item[$find] === $value) {
	           unset($array[$index]);
	       }
	   }
	   return $array;
	}

	public function sortTimings()
	{
		$timings = $this->timings;

		$time = array();

		foreach ($timings as $key => $row)
		{
		    $time[$key] = $row['time'];
		}

		array_multisort($time, SORT_ASC, $timings);

		$this->timings = $timings;
	}

	public function checkPastTimes()
	{
	   	$timings = $this->timings;

		foreach ($timings as $key => $timing)
		{
			$carbonObject = Carbon::parse($this->date.' '.$timing['time']);

			if($carbonObject->isPast())
			{
				$timing['available'] = false;
			}

			$timings[$key] = $timing;
		}

	    $this->timings = $timings;

	}

	public function generateSlots($slotSize)
	{
		$timings = $this->timings;

		// We should keep only such free which have 120 mins of time from here.
		foreach($timings as $index => $timing)
		{

			$timing['bookable'] = false;

			$goIndex = $index+1;

			if(isset($timings[$goIndex]))
			{
				$timing['end_time'] = $timings[$goIndex]['time'];
			}

			if($timing['available'])
			{
				$hasMinutesAhead = $this->hasMinutesAhead($index, $slotSize);

				if(!$hasMinutesAhead){
					$timing['bookable'] = false;
				}else{
					$timing['bookable'] = true;
					$timing['end_time'] = $timings[$hasMinutesAhead]['time'];
				}
			}

			$timings[$index] = $timing;
		}

		$this->timings = $timings;

	}

	public function hasMinutesAhead($index, $minutes)
	{
		$timings = $this->timings;
		$remainingMinutes = $minutes;

		$startTime = Carbon::createFromTimestamp(strtotime($timings[$index]['time']));

		// var_dump("need ".$minutes);
		// var_dump("checking for ".$timings[$index]['time']);

		while($remainingMinutes > 0)
		{
			$index++;

			if(!isset($timings[$index]))
			{
				break;
			}

			$next = $timings[$index];

			if(!$next['available'])
			{
				break;
			}

			// var_dump($next['time']);

			$nextTime = Carbon::createFromTimestamp(strtotime($next['time']));
			$accumulated = $nextTime->diffInMinutes($startTime);
			// var_dump($accumulated." mins");
			$remainingMinutes = $minutes - $accumulated;
			// var_dump($remainingMinutes." remaining");
		}

		if($remainingMinutes > 0)
		{
			return false;
		}

		return $index;

	}

	public function findAvailableTimings()
	{
		$timings = $this->timings;

		foreach($this->availabilities as $availability)
		{
			$startTime = Carbon::createFromTimestamp(strtotime($availability->start_time));
			$endTime = Carbon::createFromTimestamp(strtotime($availability->end_time));

			while($startTime->diffInMinutes($endTime, false) >= 0)
			{
				$timings = $this->searchForId('time',$startTime->format('H:i:s'),$timings);

				$timings[] = ['time' => $startTime->format('H:i:s'), 'available' => true];
				$startTime->addMinutes($this->interval);
			}
		}

		$this->timings = $timings;
	}

	public function markBlockedDates()
	{
		$timings = $this->timings;
		$startOfDay = Carbon::parse($this->date)->startOfDay();
		$endOfDay = Carbon::parse($this->date)->endOfDay();

		$query = BlockedTiming::where('entity_id',$this->entity->id);
		$query = Helpers::addDateRangeFilterQuery($query,'start_time','end_time',
			$startOfDay,$endOfDay);
		$blockedTimings = $query->get();

		foreach($timings as $index => $timing)
		{
			$forDate = Carbon::parse($this->date.' '.$timing['time']);

			$timing['available'] = !$this->isBusy($forDate, $blockedTimings);

			$timings[$index] = $timing;
		}

		$this->timings = $timings;
	}

	public function markBusyTimings()
	{
		$availableTimings = array_filter($this->timings, function($timing){
			return $timing['available'];
		});

		$startOfDay = Carbon::parse($this->date)->startOfDay();
		$endOfDay = Carbon::parse($this->date)->endOfDay();

		$query = Event::where('entity_id',$this->entity->id);
		$query = Helpers::addDateRangeFilterQuery($query,'start_time','end_time',
			$startOfDay,$endOfDay);
		$events = $query->get();

		foreach($availableTimings as $index => $availableTime)
		{
			$forDate = Carbon::parse($this->date.' '.$availableTime['time']);

			$availableTime['available'] = !$this->isBusy($forDate, $events);

			if(isset($availableTime['bookable']))
			{
				$availableTime['bookable'] = $availableTime['available'];
			}

			$this->timings[$index] = $availableTime;
		}
		
	}

	public function markIgnoredTimings($ignoreFrom, $ignoreTo)
	{
		$busyTimings = array_filter($this->timings, function($timing){
			return !$timing['bookable'];
		});

		foreach($busyTimings as $index => $busyTime)
		{
			if($busyTime['time'] >= $ignoreFrom && $busyTime['time'] <= $ignoreTo)
			{	
				$busyTime['bookable'] = true;
				$busyTime['available'] = true;
				$this->timings[$index] = $busyTime;
			}
		}

	}

	public function isBusy($date, $items)
	{
		foreach($items as $item)
		{
			$isBetween = $date->between($item->start_time, $item->end_time);
			if($isBetween)
			{
				return true;
			}
		}

		return false;
	}


}