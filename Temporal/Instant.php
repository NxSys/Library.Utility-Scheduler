<?php

namespace NxSys\Library\Utility\Scheduler\Temporal;

use NxSys\Library\Utility\Scheduler\TemporalInterface;
use NxSys\Library\Utility\Scheduler\Item;
use NxSys\Library\Utility\Scheduler\Exception;

abstract class Instant implements TemporalInterface
{
	abstract public static function getMap() : array;
	
	abstract public static function getName() : string;
	
	public static function count(TemporalInterface $oRecurrence) : int
	{
		if (!static::contains($oRecurrence))
		{
			throw new Exception\TemporalException(static::getName() . 's' . ' do not contain ' . $oRecurrence::getName() . 's.');
		}
		
		return static::getMap()[$oRecurrence::getName()];
	}
	
	public static function contains(TemporalInterface $oRecurrence) : bool
	{
		$sRecurrence = $oRecurrence::getName();
		
		if (array_key_exists($sRecurrence, static::getMap()))
		{
			return true;
		}
		
		return false; 
	}
	
		
	public static function getItems(TemporalInterface $oRecurrence, int $iInterval, int $iStart, int $iEnd) : array
	{
		if ($iInterval < 0 || $iStart < 0 || $iEnd < 0)
		{
			throw new Exception\NotImplementedException("Negative intervals or bounds not yet implemented.");
		}
		
		$iItems = [];
		
		for ($iCurrent = $iStart; $iCurrent < $iEnd; $iCurrent += $iInterval)
		{
			$sCalledClass = get_called_class();
			$iItems[] = new Item($oRecurrence, new $sCalledClass, $iCurrent);
		}
		
		return $iItems;
	}
	
	
	public static function checkMatch(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : bool
	{
	
	}
	
	public static function doReset(TemporalInterface $oRecurrence) : bool
	{
		return true;
	}
}