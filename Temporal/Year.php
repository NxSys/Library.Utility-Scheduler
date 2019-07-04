<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;
use NxSys\Library\Utility\Scheduler\Exception;

class Year extends Instant implements TemporalInterface
{
	public static function getName() : string
	{
		return "Year";
	}
	
	public static function getMap() : array
	{
		return ["Second" => 37800000,
				"Minute" => 630000,
				"Hour" => 10500,
				"Day" => 420,
				"Week" => 60,
				"Month" => 12];
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
			$iDay = (int) $dDate->format('z');
			return $iSecond + ($iMinute * 60) + ($iHour * (60*60)) + ($iDay * (24*60*60)) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			//@TODO: Check for DST
			$iMinute = (int) $dDate->format('i');
			$iHour = (int) $dDate->format('H');
			$iDay = (int) $dDate->format('z');
			return $iMinute + ($iHour * 60) ($iDay * (24*60)) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Hour")
		{
			//@TODO: Check for DST
			$iHour = (int) $dDate->format('H');
			$iDay = (int) $dDate->format('z');
			return $iHour + ($iDay * 24) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Day")
		{
			$iDay = (int) $dDate->format('z');
			return $iDay == $iValue;
		}
		
		if ($oRecurrence::getName() == "Week")
		{
			$iWeek = (int) $dDate->format('W') - 1;
			return $iWeek == $iValue;
		}
		
		if ($oRecurrence::getName() == "Month")
		{
			$iMonth = (int) $dDate->format('m') - 1;
			return $iMonth == $iValue;
		}
	}
	
	public static function isValid(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : bool
	{
		if (!static::contains($oRecurrence))
		{
			throw new Exception\TemporalException(static::getName() . 's' . ' do not contain ' . $oRecurrence::getName() . 's.');
		}
		
		$iDaysInYear = 365 + (int) $dDate->format('L');
		
		if ($oRecurrence::getName() == "Second")
		{
			if ($iValue < 0 || $iValue > ($iDaysInYear*24*60*60 + (60*60)))
			{
				return false;	
			}
			
			if ($iValue == ($iDaysInYear*24*60*60))
			{
				return false; //@TODO: Support for leap seconds.
			}
			
			if ($iValue > ($iDaysInYear*24*60*60))
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
			if ($iValue < 0 || $iValue > ($iDaysInYear*24*60 + (60)))
			{
				return false;	
			}
			
			if ($iValue > ($iDaysInYear*24*60))
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
			if ($iValue < 0 || $iValue >= $iDaysInYear)
			{
				return false;	
			}

			return true;
		}
		
		if ($oRecurrence::getName() == "Week")
		{
			if ($iValue < 0 || $iValue > 52)
			{
				return false;	
			}
			
			if ($iValue == 52)
			{
				throw new Exception\NotImplementedException("Check to determine whether 53rd week is valid not yet implemented.");
			}
			
			return true;
		}
		
		if ($oRecurrence::getName() == "Month")
		{
			if ($iValue < 0 || $iValue > 12)
			{
				return false;	
			}
			
			return true;
		}
	}
	
	public static function modifyDate(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : \DateTime
	{
		$iSecond = (int) $dDate->format('s');
		$iMinute = (int) $dDate->format('i');
		$iHour = (int) $dDate->format('H');
		
		$iDay = (int) $dDate->format('d');
		$iMonth = (int) $dDate->format('m');
		$iYear = (int) $dDate->format('Y');
		
		$iDaysInMonth = (int) $dDate->format('t');
		
		if ($oRecurrence::getName() == "Second")
		{
			throw new Exception\NotImplementedException("Unable to set date/time based on seconds in years.");
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			throw new Exception\NotImplementedException("Unable to set date/time based on minutes in years.");
		}
		
		if ($oRecurrence::getName() == "Hour")
		{
			throw new Exception\NotImplementedException("Unable to set date/time based on hours in years.");
		}
		
		if ($oRecurrence::getName() == "Day")
		{
			throw new Exception\NotImplementedException("Unable to set date/time based on days in years.");
		}
		
		if ($oRecurrence::getName() == "Week")
		{
			throw new Exception\NotImplementedException("Unable to set date/time based on weeks in years.");
		}
		
		if ($oRecurrence::getName() == "Month")
		{
			$dDate->setDate($iYear, $iValue+1, $iDay);
		}
		
		return $dDate;
	}
	
	public static function incrementDate(\DateTime $dDate) : \DateTime
	{
		$oPeriod = new \DateInterval("P1Y");
		$dDate->add($oPeriod);
		return $dDate;
	}
	
}