<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;
use NxSys\Library\Utility\Scheduler\Exception;

class Hour extends Instant implements TemporalInterface
{
	public static function getName() : string
	{
		return "Hour";
	}
	
	public static function getMap() : array
	{
		return ["Second" => (60*60),
				"Minute" => 60];
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
			$iSecond = (int) $dDate->format('s');
			$iMinute = (int) $dDate->format('i');
			return $iSecond + ($iMinute * 60) == $iValue;
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			$iMinute = (int) $dDate->format('i');
			return $iMinute == $iValue;
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
			if ($iValue < 0 || $iValue > (60*60))
			{
				return false;	
			}
			
			if ($iValue == (60*60))
			{
				return false; //@TODO: Support for leap seconds.
			}
			
			return true;
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			if ($iValue < 0 || $iValue > 59)
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
		
		if ($oRecurrence::getName() == "Second")
		{
			$iSecond = $iValue % 60;
			$iMinute = (int) ($iValue / 60);
			$dDate->setTime($iHour, $iMinute, $iSecond);
			return $dDate;
		}
		
		if ($oRecurrence::getName() == "Minute")
		{
			$dDate->setTime($iHour, $iValue, $iSecond);
			return $dDate;
		}
		
		return $dDate;
	}
	
	public static function incrementDate(\DateTime $dDate) : \DateTime
	{
		$oPeriod = new \DateInterval("PT1H");
		$dDate->add($oPeriod);
		return $dDate;
	}
}