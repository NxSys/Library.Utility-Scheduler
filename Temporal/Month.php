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
		
		$iMonth = (int) $dDate->format('m');
		$iYear = (int) $dDate->format('Y');
		$dFirstDay = clone $dDate;
		$dFirstDay->setDate($iYear, $iMonth, 1);
		
		$iDayOfWeek = (int) $dFirstDay->format('w');
		
		$iWeeksInMonth = (int) ceil(($iDaysInMonth - 1 + $iDayOfWeek)/7);
		
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
			if ($iValue < 0 || $iValue > $iWeeksInMonth)
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
			//@TODO: Handle DST
			$iSecond = (int) ($iValue % 60);
			$iMinute = (int) (($iValue % 3600) / 60);
			$iHour = (int) (($iValue % 86400) / 3600);
			$iDay = (int) ($iValue / 86400) + 1;
			$dDate->setTime($iHour, $iMinute, $iSecond);
			$dDate->setDate($iYear, $iMonth, $iDay);
			return $dDate;
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			//@TODO: Handle DST
			$iMinute = (int) ($iValue % 60);
			$iHour = (int) (($iValue % 1440) / 60);
			$iDay = (int) ($iValue / 1440) + 1;
			$dDate->setTime($iHour, $iMinute, $iSecond);
			$dDate->setDate($iYear, $iMonth, $iDay);
			return $dDate;
		}
		
		if ($oRecurrence::getName() == "Hour")
		{
			//@TODO: Handle DST
			$iHour = (int) ($iValue % 24);
			$iDay = (int) ($iValue / 24) + 1;
			$dDate->setTime($iHour, $iMinute, $iSecond);
			$dDate->setDate($iYear, $iMonth, $iDay);
			return $dDate;
		}
		
		if ($oRecurrence::getName() == "Day")
		{
			$dDate->setDate($iYear, $iMonth, $iValue + 1);
			return $dDate;
		}
		
		if ($oRecurrence::getName() == "Week")
		{
			$iDay = $iValue * 7 + 1;
			$dDate->setDate($iYear, $iMonth, $iDay);
			return $dDate;
		}
		
		return $dDate;
	}
	
	public static function incrementDate(\DateTime $dDate) : \DateTime
	{
		$oPeriod = new \DateInterval("P1M");
		$dDate->add($oPeriod);
		return $dDate;
	}
	
}