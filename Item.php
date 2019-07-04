<?php

namespace NxSys\Library\Utility\Scheduler;

class Item
{
	public function __construct(TemporalInterface $oRecurrence,
								TemporalInterface $oContainer,
								int $iValue)
	{
		$this->Container = $oContainer;
		$this->Recurrence = $oRecurrence;
		$this->Value = $iValue;
	}
	
	public function checkMatch(\DateTime $dDate = null) : bool
	{
		return $this->Container::checkMatch($this->Recurrence, $this->Value, $dDate);
	}
	
	public function isValid(\DateTime $dDate = null) : bool
	{
		return $this->Container::isValid($this->Recurrence, $this->Value, $dDate);
	}
	
	public function modifyDate(\DateTime $dDateTime = null) : \DateTime
	{
		if ($dDateTime == null)
		{
			$dDateTime = new \DateTime();
		}
		
		return $this->Container::modifyDate($this->Recurrence, $this->Value, $dDateTime);
	}
}