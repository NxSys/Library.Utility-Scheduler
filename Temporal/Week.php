<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;
use NxSys\Library\Utility\Scheduler\Exception;

class Week extends Instant implements TemporalInterface
{
	public static function getName() : string
	{
		return "Week";
	}
	
	public static function getMap() : array
	{
		return ["Second" => (7*25*60*60),
				"Minute" => (7*25*60),
				"Hour" => (7*25),
				"Day" => 7];
	}
	
	public static function checkMatch(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : bool
	{
		if (!static::contains($oRecurrence))
		{
			throw new Exception\TemporalException(static::getName() . 's' . ' do not contain ' . $oRecurrence::getName() . 's.');
		}
		
		if ($oRecurrence::getName() == "Second")
		{
			//@TODO: Check for leap seconds
			//@TODO: Check for DST
			$iSecond = (int) $dDate->format('s');
			$iMinute = (int) $dDate->format('i');
			$iHour = (int) $dDate->format('H');
			$iDay = (int) $dDate->format('w');
			return $iSecond + ($iMinute * 60) + ($iHour * (60*60)) + ($iDay * (24*60*60)) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			//@TODO: Check for DST
			$iMinute = (int) $dDate->format('i');
			$iHour = (int) $dDate->format('H');
			$iDay = (int) $dDate->format('w');
			return $iMinute + ($iHour * 60) ($iDay * (24*60)) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Hour")
		{
			//@TODO: Check for DST
			$iHour = (int) $dDate->format('H');
			$iDay = (int) $dDate->format('w');
			return $iHour + ($iDay * 24) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Day")
		{
			$iDay = (int) $dDate->format('w');
			return $iDay == $iValue;
		}
	}
	
	public static function isValid(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : bool
	{
		if (!static::contains($oRecurrence))
		{
			throw new Exception\TemporalException(static::getName() . 's' . ' do not contain ' . $oRecurrence::getName() . 's.');
		}
		
		if ($oRecurrence::getName() == "Second")
		{
			if ($iValue < 0 || $iValue > (7*24*60*60 + (60*60)))
			{
				return false;	
			}
			
			if ($iValue == (7*24*60*60))
			{
				return false; //@TODO: Support for leap seconds.
			}
			
			if ($iValue > (7*24*60*60))
			{
				return false; //@TODO: Support for DST (25th hour)
			}
			
			if (false)
			{
				return false; //@TODO: Support for DST (24th hour on days with 23 hours.)
			}
			
			return true;
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			if ($iValue < 0 || $iValue > (7*24*60+60))
			{
				return false;	
			}
			
			if ($iValue > (7*24*60))
			{
				return false; //@TODO: Support for DST (25th hour)
			}
			
			if (false)
			{
				return false; //@TODO: Support for DST (24th hour on days with 23 hours.)
			}
			
			return true;
		}
		
		if ($oRecurrence::getName() == "Hour")
		{
			if ($iValue < 0 || $iValue > (24))
			{
				return false;	
			}
			
			if ($iValue == (24))
			{
				return false; //@TODO: Support for DST (25th hour)
			}
			
			if ($iValue == (23) && false)
			{
				return false; //@TODO: Support for DST (24th hour on days with 23 hours.)
			}
			
			return true;
		}
		
		if ($oRecurrence::getName() == "Day")
		{
			if ($iValue < 0 || $iValue > 6)
			{
				return false;	
			}

			return true;
		}
	}
	
	public static function modifyDate(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : \DateTime
	{
		throw new Exception\NotImplementedException("Unable to set date/time based on weeks.");
	}
	
	public static function resetDate(\DateTime $dDate) : \DateTime
	{
		throw new Exception\NotImplementedException("Looping over weeks not yet implemented.");
	}
	
	public static function incrementDate(\DateTime $dDate) : \DateTime
	{
		throw new Exception\NotImplementedException("Incrementing weeks not yet implemented.");
	}
}