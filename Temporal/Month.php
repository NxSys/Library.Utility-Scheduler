<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;

class Month implements TemporalInterface
{
	public static function getName() : string
	{
		return "Month";
	}
	
	public static function isVariable() : bool
	{
		return True;
	}
}