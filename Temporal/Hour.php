<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;

class Hour implements TemporalInterface
{
	public static function getName() : string
	{
		return "Hour";
	}
	
	public static function isVariable() : bool
	{
		return False;
	}
}