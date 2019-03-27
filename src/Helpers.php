<?php

namespace Betalectic\Ocupado;

class Helpers {

	public static function anyOverlaps($items)
	{
        $overlaps = [];
		$result = false;

		foreach ($items as $index => $item) {

			$tempItems = $items;
			unset($tempItems[$index]); // Remaining items

			foreach($tempItems as $tempItem)
			{
				$result = static::isOverlapping($item['start_time'], $item['end_time'], $tempItem['start_time'], $tempItem['end_time']);

				if($result)
				{
					$overlaps[] = $item;
				}

			}

		}

		return $overlaps;
	}

    public static function isOverlapping($findFrom, $findTo, $rangeFrom, $rangeTo)
    {
		$findFrom = intval(str_replace(':', '', $findFrom));
		$findTo = intval(str_replace(':', '', $findTo));
		$rangeFrom = intval(str_replace(':', '', $rangeFrom));
		$rangeTo = intval(str_replace(':', '', $rangeTo));

        if($findFrom > $rangeFrom && $findFrom < $rangeTo) {
            return true;
        } else if($findTo > $rangeFrom && $findTo < $rangeTo) {
            return true;
        } else if($findFrom < $rangeFrom && $findTo > $rangeTo) {
            return true;
        } else if($findFrom == $rangeFrom && $findTo == $rangeTo) {
            return true;
        }

        return false;
    }

    public static function addDateRangeFilterQuery($query, $startColumn, $endColumn, $startValue, $endValue)
    {

		$query = $query->where(function($query) use ($startColumn, $endColumn, $startValue, $endValue) {
			$query->where(function($query) use ($startColumn, $endColumn, $startValue, $endValue) {
				$query
				->where($startColumn,'<',$startValue)
				->where($endColumn,'>',$startValue);
			})
			->orWhere(function($query) use ($startColumn, $endColumn, $startValue, $endValue) {
				$query
				->where($startColumn,'<',$endValue)
				->where($endColumn,'>',$endValue);
			})
			->orWhere(function($query) use ($startColumn, $endColumn, $startValue, $endValue) {
				$query
				->where($startColumn,'>=',$startValue)
				->where($endColumn,'<=',$endValue);
			});
		});

    	return $query;
    }

    public static function addDateFilterQuery($query, $startColumn, $endColumn, $date)
    {

		$query = $query->where($startColumn,'<',$date)
					->where($endColumn,'>',$date);

    	return $query;
    }

}

