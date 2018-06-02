<?php

namespace NxSys\Library\Utility\Scheduler;

interface TemporalInterface
{	
	public static function getName() : string;
	
	public static function isVariable() : bool;
	
	public static function contains(TemporalInterface $oRecurrence) : bool;
	
	public function count(TemporalInterface $oRecurrence) : int;
	
	public function getItems(TemporalInterface $oRecurrence, int $iInterval, int $iStart, int $iEnd) : array;
	
	public function checkMatch(TemporalInterface $oRecurrence, int $iValue, \DateTime $dDate) : bool;
	
	//Set the reference datetime for variable containers, such as months.
	public function __invoke(\DateTime $dDate) : self;
}