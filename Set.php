<?php

namespace NxSys\Library\Utility\Scheduler;

class Set
{
	public function __construct(TemporalInterface $oRecurrence,
								TemporalInterface $oContainer,
								array $iValues = [])
	{
		$this->Recurrence = $oRecurrence;
		$this->Container = $oContainer;
		
		$this->Items = [];
		
		foreach ($iValues as $iValue)
		{
			$this->addItem($iValue);
		}
	}
	
	public function addItem(int $iValue)
	{
		$this->Items[] = new Item($this->Recurrence, $this->Container, $iValue);
	}
	
	public function getItems() : array
	{
		return $this->Items;
	}
	
	public function count() : int
	{
		return count($this->Items);
	}
}