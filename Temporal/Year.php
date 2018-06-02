<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;

class Year implements TemporalInterface
{
	public static function getName() : string
	{
		return "Year";
	}
	
	public static function isVariable() : bool
	{
		return True; //Leap years...
	}
}