This package can be used to find "availability" of an "entity".

Entity can be a person, room or anything which can be booked.

The first things which should be setup is availability:

* Tell the application when is an entity available for booking
	* On each weekday -> monday, tuesday.. etc., at what times it's available
	* There might be special constraints during each available timings also.
	* Example: 
		* On monday 9:00 13:00 a doctor is available to meet in person
		* On monday 14:00 18:00 a doctor is available to meet online
	* Also tell at what times an entity is not available, to override availability. This helps to mark "not available" timings. Which might be holidays. Once you set your weekday availability, it's possible that might want to block some dates out of it.

Register
=====

Occupado::registerEntity($doctor); // This is to register the doctor

Set Availability
======

Occupado::setDayAvailability($doctor, $monday, [
	[ 09:00, 13:00, []],
	[ 14:00, 18:00, []]
], $startDate, $endDate); 

Conflicting timings won't be allowed

You use this API to add/edit/remove availability for a day for an entity.

Occupado::setDayAvailability($doctor, $monday, []); // Makes them available from no where.

startDate and endDate are optional.

Occupado:blockAvailability($doctor, $startTime, $endTime);
Occupado:getBlockedTimings($doctor);
Occupado:removeBlockedTiming($timingId);

============

The second part of the application is telling, when is someone busy and with what?

Occupado::createEvent($doctor, $startTime, $endTime, $meta);
Occupado::updateEvent($eventId, $startTime, $endTime, $meta);
Occupado::deleteEvent($eventId);

The above APIs are pretty straight forward and give you an idea on how to set events (busy timings) of an entity.

=============

Now comes the meat of the application

Get Available Timings
======

Occupado::getAvailability($doctor);

This brings your availability, By default it gives you availability as a summary. This does
not consider busy timings.

Occupado::getEvents($doctor, $startTime, $endTime);

Returns all the event with which doctor is busy.

Occupado::getAvailability($doctor,$startDate,$endDate);

This one responds with available timings in one hour slots between those dates. Let's say if doctor is available from 9-11, it would tell you that doctor is available from

9:00 - 10:00
10:00 - 11:00

The default slot size is 60 mins.

You can increase or decrease using 

Occupado::getAvailability($doctor,$startDate,$endDate, 60/30/15/120);

Let's say your use has to book 3 hour slots and nothing more/less.

You call, 

Occupado::getAvailability($doctor,$startDate,$endDate, 180);

ignoreSlots

As per above example, we would get no available slots, unless there is 3 hour gap.

-------



