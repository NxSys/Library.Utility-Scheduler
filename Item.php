<?php

namespace NxSys\Library\Utility\Scheduler;

class Item
{
	public function __construct(TemporalInterface $oContainer,
								TemporalInterface $oRecurrence,
								int $iValue)
	{
		$this->Container = $oContainer;
		$this->Recurrence = $oRecurrence;
		$this->Value = $iValue;
	}
	
	public function checkMatch(\DateTime $dDate = null) : bool
	{
		return $this->Container->checkMatch($this->Recurrence, $this->Value, $dDate);
	}
}