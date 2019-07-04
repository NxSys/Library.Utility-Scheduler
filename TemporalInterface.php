<?php

namespace NxSys\Library\Utility\Scheduler;

interface TemporalInterface
{
	public static function getName() : string;
	
	public static function getMap() : array;
	
	public static function count(TemporalInterface $oRecurrence) : int;
	
	public static function contains(TemporalInterface $oRecurrence) : bool;
	
	public static function getItems(TemporalInterface $oRecurrence, int $iInterval, int $iStart, int $iEnd) : array;
	
	public static function checkMatch(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : bool;
	
	public static function isValid(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : bool;
	
	public static function modifyDate(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : \DateTime;
	
	public static function incrementDate(\DateTime $dDate) : \DateTime;
	
	public static function doReset(TemporalInterface $oRecurrence);
}