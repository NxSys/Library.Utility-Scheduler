<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;

class Week implements TemporalInterface
{
	public static function getName() : string
	{
		return "Week";
	}
	
	public static function isVariable() : bool
	{
		return False;
	}
}