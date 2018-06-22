<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;
use NxSys\Library\Utility\Scheduler\Exception;

class Second extends Instant implements TemporalInterface
{
	public static function getName() : string
	{
		return "Second";
	}
	
	public static function getMap() : array
	{
		return []; //Seconds do not contain anything. (No milli- or micro-seconds atm.)
	}
	
	public static function getItems(TemporalInterface $oRecurrence, int $iInterval, int $iStart, int $iEnd) : array
	{
		throw new Exception\TemporalException("Seconds are currently the smallest available temporal unit.");
	}
	
	public static function checkMatch(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : bool
	{
		return	true; //It is always a second.
	}
	
	public static function isValid(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : bool
	{
		return	true; //Anything in a second are always valid.
	}
	
	public static function modifyDate(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : \DateTime
	{
		throw new Exception\TemporalException("Seconds are currently the smallest available temporal unit.");
	}
	
	public static function incrementDate(\DateTime $dDate) : \DateTime
	{
		$oPeriod = new \DateInterval("PT1S");
		$dDate->add($oPeriod);
		return $dDate;
	}
}