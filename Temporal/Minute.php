<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;

class Minute implements TemporalInterface
{
	public static function getName() : string
	{
		return "Minute";
	}
	
	public static function isVariable() : bool
	{
		return False;
	}
}