<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;

class Day implements TemporalInterface
{
	public static function getName() : string
	{
		return "Day";
	}
	
	public static function isVariable() : bool
	{
		return True; //Daylight savings...
	}
}