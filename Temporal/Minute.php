<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;
use NxSys\Library\Utility\Scheduler\Exception;

class Minute extends Instant implements TemporalInterface
{
	public static function getName() : string
	{
		return "Minute";
	}
	
	public static function getMap() : array
	{
		return ["Second" => 60];
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
			return $iSecond == $iValue;
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
			if ($iValue < 0 || $iValue > 60)
			{
				return false;	
			}
			
			if ($iValue == 60)
			{
				return false; //@TODO: Support for leap seconds.
			}
			
			return true;
		}
	}
	
	public static function modifyDate(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : \DateTime
	{
		$iMinute = (int) $dDate->format('i');
		$iHour = (int) $dDate->format('H');
		
		if ($oRecurrence::getName() == "Second")
		{
			$dDate->setTime($iHour, $iMinute, $iValue);
			return $dDate;
		}
		
		return $dDate;
	}
	
	public static function incrementDate(\DateTime $dDate) : \DateTime
	{
		$oPeriod = new \DateInterval("PT1M");
		$dDate->add($oPeriod);
		return $dDate;
	}
}