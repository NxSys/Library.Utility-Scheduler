<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;
use NxSys\Library\Utility\Scheduler\Exception;

class Month extends Instant implements TemporalInterface
{
	public static function getName() : string
	{
		return "Month";
	}
	
	public static function getMap() : array
	{
		return ["Second" => 3150000,
				"Minute" => 52500,
				"Hour" => 875,
				"Day" => 35,
				"Week" => 5];
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
			$iDay = (int) $dDate->format('d') - 1;
			return $iSecond + ($iMinute * 60) + ($iHour * (60*60)) + ($iDay * (24*60*60)) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			//@TODO: Check for DST
			$iMinute = (int) $dDate->format('i');
			$iHour = (int) $dDate->format('H');
			$iDay = (int) $dDate->format('d') - 1;
			return $iMinute + ($iHour * 60) ($iDay * (24*60)) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Hour")
		{
			//@TODO: Check for DST
			$iHour = (int) $dDate->format('H');
			$iDay = (int) $dDate->format('d') - 1;
			return $iHour + ($iDay * 24) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Day")
		{
			$iDay = (int) $dDate->format('d') - 1;
			return $iDay == $iValue;
		}
		
		if ($oRecurrence::getName() == "Week")
		{
			throw new Exception\NotImplementedException("Weeks in Months not yet implemented.");	
		}
	}
	
	public static function isValid(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : bool
	{
		if (!static::contains($oRecurrence))
		{
			throw new Exception\TemporalException(static::getName() . 's' . ' do not contain ' . $oRecurrence::getName() . 's.');
		}
		
		$iDaysInMonth = (int) $dDate->format('t');
		
		if ($oRecurrence::getName() == "Second")
		{
			if ($iValue < 0 || $iValue > ($iDaysInMonth*24*60*60 + (60*60)))
			{
				return false;	
			}
			
			if ($iValue == ($iDaysInMonth*24*60*60))
			{
				return false; //@TODO: Support for leap seconds.
			}
			
			if ($iValue > ($iDaysInMonth*24*60*60))
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
			if ($iValue < 0 || $iValue > ($iDaysInMonth*24*60 + (60)))
			{
				return false;	
			}
			
			if ($iValue > ($iDaysInMonth*24*60))
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
			if ($iValue < 0 || $iValue > ($iDaysInMonth*24))
			{
				return false;	
			}
			
			if ($iValue == ($iDaysInMonth*24))
			{
				return false; //@TODO: Support for DST (25th hour)
			}
			
			if ($iValue == ($iDaysInMonth*24) - 1 && false)
			{
				return false; //@TODO: Support for DST (24th hour on days with 23 hours.)
			}
			
			return true;
		}
		
		if ($oRecurrence::getName() == "Day")
		{
			if ($iValue < 0 || $iValue >= $iDaysInMonth)
			{
				return false;	
			}

			return true;
		}
		
		if ($oRecurrence::getName() == "Week")
		{
			throw new Exception\NotImplementedException("Weeks in Months not yet implemented.");	
		}
	}
	
	public static function modifyDate(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : \DateTime
	{
		throw new Exception\NotImplementedException("Unable to set date/time based on months.");
	}
	
}