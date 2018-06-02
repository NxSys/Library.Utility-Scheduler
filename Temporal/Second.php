<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;

class Second implements TemporalInterface
{
	public static function getName() : string
	{
		return "Second";
	}
	
	public static function isVariable() : bool
	{
		return False;
	}
}