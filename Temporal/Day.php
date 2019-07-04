<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;
use NxSys\Library\Utility\Scheduler\Exception;

class Day extends Instant implements TemporalInterface
{
	public static function getName() : string
	{
		return "Day";
	}
	
	public static function getMap() : array
	{
		return ["Second" => (25*60*60),
				"Minute" => (25*60),
				"Hour" => 25]; //Days can have *up to* 25 hours, see daylight savings time. Doesn't matter if some of the triggers are invalid times.
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
			return $iSecond + ($iMinute * 60) + ($iHour * (60*60)) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			//@TODO: Check for DST
			$iMinute = (int) $dDate->format('i');
			$iHour = (int) $dDate->format('H');
			return $iMinute + ($iHour * 60) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Hour")
		{
			//@TODO: Check for DST
			$iHour = (int) $dDate->format('H');
			return $iHour == $iValue;
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
			if ($iValue < 0 || $iValue > (25*60*60))
			{
				return false;	
			}
			
			if ($iValue == (24*60*60))
			{
				return false; //@TODO: Support for leap seconds.
			}
			
			if ($iValue > (24*60*60))
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
			if ($iValue < 0 || $iValue > (25*60))
			{
				return false;	
			}
			
			if ($iValue > (24*60))
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
			if ($iValue < 0 || $iValue > 24)
			{
				return false;	
			}
			
			if ($iValue == 24)
			{
				return false; //@TODO: Support for  DST (25th hour)
			}
			
			if (false)
			{
				return false; //@TODO: Support for DST (24th hour on days with 23 hours.)
			}
			
			return true;
		}
	}
	
	public static function modifyDate(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : \DateTime
	{	
		$iSecond = (int) $dDate->format('s');
		$iMinute = (int) $dDate->format('i');
		$iHour = (int) $dDate->format('H');
		
		if ($oRecurrence::getName() == "Second")
		{
			$iSecond = $iValue % 60;
			$iMinute = (int) (($iValue % 3600) / 60);
			$iHour = (int) ($iValue / 3600);
			$dDate->setTime($iHour, $iMinute, $iSecond);
			return $dDate;
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			$iMinute = (int) ($iValue % 60);
			$iHour = (int) ($iValue / 60);
			$dDate->setTime($iHour, $iMinute, $iSecond);
			return $dDate;
		}
		
		if ($oRecurrence::getName() == "Hour")
		{
			$dDate->setTime($iValue, $iMinute, $iSecond);
			return $dDate;
		}
		
		return $dDate;
	}
	
	public static function incrementDate(\DateTime $dDate) : \DateTime
	{
		$oPeriod = new \DateInterval("P1D");
		$dDate->add($oPeriod);
		return $dDate;
	}
}