<?php

namespace Betalectic\Ocupado;

use Illuminate\Support\Facades\Storage;
use Betalectic\Ocupado\Models\Entity;
use Betalectic\Ocupado\Models\Availability;
use Betalectic\Ocupado\Models\Event;
use Betalectic\Ocupado\Models\BlockedTiming;
use Betalectic\Ocupado\Helpers;
use Carbon\Carbon;

class Ocupado {

	public function __construct()
	{

	}

	public function createEvent($entity, $eventId, $startTime, $endTime, $meta = [])
	{
		$entity = $this->registerEntity($entity);

		$builder = new Event();
		$builder = Helpers::addDateRangeFilterQuery($builder, 'start_time','end_time',$startTime, $endTime);
		$found = $builder->get();

		if($found->count()){
			return 'An event exists during this time';
		}

		return Event::create([
            'uuid' => $eventId,
			'entity_id' => $entity->id,
			'start_time' => Carbon::parse($startTime),
			'end_time' => Carbon::parse($endTime),
			'meta' => $meta
		]);

	}


	public function updateEvent($eventId, $startTime, $endTime, $meta = [])
	{

		$builder = new Event();
		$builder = Helpers::addDateRangeFilterQuery($builder, 'start_time','end_time',$startTime, $endTime);
		$found = $builder->whereNotIn('uuid',[$eventId])->get();

		if($found->count()){
			return 'An event exists during this time';
		}

		$event = Event::whereUuid($eventId)->first();

		$event->fill([
			'start_time' => Carbon::parse($startTime),
			'end_time' => Carbon::parse($endTime),
			'meta' => $meta
		]);

		$event->save();

		return $event->fresh();

	}

    public function createOrUpdate($entity, $eventId, $startTime, $endTime, $meta = [])
    {
        $entity = $this->registerEntity($entity);

        $builder = new Event();
        $builder = Helpers::addDateRangeFilterQuery($builder, 'start_time','end_time',$startTime, $endTime);
        $found = $builder->whereNotIn('uuid',[$eventId])->get();

        if($found->count()){
            return 'An event exists during this time';
        }

        $event = Event::firstOrCreate([
            'uuid' => $eventId,
            'entity_id' => $entity->id,
        ]);

        $event->fill([
            'start_time' => Carbon::parse($startTime),
            'end_time' => Carbon::parse($endTime),
            'meta' => $meta
        ]);

        $event->save();

        return $event->fresh();
    }

    public function deleteEvent($entity, $eventId)
    {
        $entity = $this->registerEntity($entity);

        $event = Event::where([
            'uuid' => $eventId,
            'entity_id' => $entity->id,
        ])->first();

        if(!is_null($event))
        {
            $event->delete();
        }
        return true;
    }

    public function findOverlaps($timings)
    {
        $overlaps = Helpers::anyOverlaps($timings);
        return $overlaps;
    }

	public function registerEntity($entity)
	{
		return Entity::firstOrCreate([
			'type' => get_class($entity),
			'value' => $entity->getKey()
		]);
	}

	public function setDayAvailability($entity, $daysOfWeek,
		$timings, $fromDate = NULL, $toDate = NULL)
	{
		$overlap = Helpers::anyOverlaps($timings);

		if($overlap)
		{
			return 'overlapping timings';
		}

		/*
			If dates are mentioned we have to check if there are any overlapping dates
			and reject. What should they then? Let's not support now.
		*/

		$entity = $this->registerEntity($entity);

		foreach($daysOfWeek as $dayOfWeek)
		{
			Availability::where([
				'entity_id' => $entity->id,
				'day_of_week' => $dayOfWeek
			])->delete();

			foreach($timings as $timing)
			{
				Availability::create([
					'entity_id' => $entity->id,
					'day_of_week' => $dayOfWeek,
					'start_time' => $timing['start_time'],
					'end_time' => $timing['end_time'],
				]);
			}

		}

		return true;

	}

	public function blockTiming($entity, $startTime, $endTime, $meta = [])
	{
		$entity = $this->registerEntity($entity);

		$builder = new BlockedTiming();
		$builder = Helpers::addDateRangeFilterQuery($builder, 'start_time','end_time',$startTime, $endTime);
		$found = $builder->get();

		if($found->count()){
			return 'Time is already blocked';
		}

		return BlockedTiming::create([
			'entity_id' => $entity->id,
			'start_time' => Carbon::parse($startTime),
			'end_time' => Carbon::parse($endTime),
			'meta' => $meta
		]);
	}

	public function updateBlockTiming($timingId, $startTime, $endTime, $meta = [])
	{

		$builder = new BlockedTiming();
		$builder = Helpers::addDateRangeFilterQuery($builder, 'start_time','end_time',$startTime, $endTime);
		$found = $builder->whereNotIn('uuid',[$timingId])->get();

		if($found->count()){
			return 'An event exists during this time';
		}

		$timing = BlockedTiming::whereUuid($timingId)->first();

		$timing->fill([
			'start_time' => Carbon::parse($startTime),
			'end_time' => Carbon::parse($endTime),
			'meta' => $meta
		]);

		$timing->save();

		return $timing->fresh();

	}

	public function getAvailability(AvailabilityFilter $filter)
	{
		$generator = new AvailabilityGenerator($filter);

		return $generator->timings;
	}


	// public function getAvailability(
	// 	$entity //user, tutor, doctor,
	// 	$date,
	// 	$interval = 60,
	// 	$bookingSlotSize = NULL,
	// 	$extrapolateFrom = NULL,
	// 	$extrapolateTo = NULL,
	// 	$ignoreFrom = NULL,
	// 	$ignoreTo = NULL,
	// 	$ignoreBlockedDates = false
	// )
	// {

	// }

}
