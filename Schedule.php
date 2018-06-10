<?php

namespace NxSys\Library\Utility\Scheduler;

class Schedule
{
	protected $Rules;
	
	/**
	 * @param array $aRules An array of Rule objects defining the schedule.
	 */
	public function __construct(array $aRules = [])
	{
		$this->Rules = [];
		
		foreach ($aRules as $oRule)
		{
			$this->addRule($oRule);
		}
		
		$this->Items = null;
		$this->Intersections = null;
		
		$this->TemporalPrecedence = ["Second" => 0,
									 "Minute" => 1,
									 "Hour" => 2,
									 "Day" => 3,
									 "Week" => 4,
									 "Month" => 5,
									 "Year" => 6];
		
	}
	
	public function addRule(Rule $oRule)
	{
		$sRuleName = $oRule->getName();
		if (array_key_exists($sRuleName, $this->Rules))
		{
			$this->Rules[$sRuleName]->reduce($oRule);
		}
		else
		{
			$this->Rules[$sRuleName] = $oRule;
		}
	}
	
	public function getOccurrences(int $iAmount, \DateTime $dDateTime = null) : array
	{
		$aOccurrences = [];
		
		if ($dDateTime == null)
		{
			$dDateTime = new \DateTime();
		}
		
		$dNextDate = $dDateTime;
		
		while (count($aOccurrences) < $iAmount)
		{
			$dNextDate = $this->getNextOccurrence($dNextDate);
			$aOccurrences[] = $dNextDate;
			$dNextDate = clone $dNextDate;
			$dNextDate = Temporal\Second::incrementDate($dNextDate);
		}
		
		return $aOccurrences;
	}
	
	public function getNextOccurrence(\DateTime $dDateTime = null) : \DateTime
	{
		if ($dDateTime == null)
		{
			$dDateTime = new \DateTime();
		}
		
		$dReferenceDateTime = clone $dDateTime;
		$dReferenceDateTime = $this->resetReferenceDate($dReferenceDateTime);
		
		$i = 0;
		$iPeriodicity = $this->getPeriodicity();
		while ($i < $iPeriodicity * 2) //If this loops twice, something is wrong.
		{
			$bIsValid = false;
			
			while(!$bIsValid)
			{
				if ($i == $iPeriodicity)
				{
					$oHighestContainer = $this->getHighestContainer();
					$dReferenceDateTime = $oHighestContainer->incrementDate($dReferenceDateTime);
					$dReferenceDateTime = $this->resetReferenceDate($dReferenceDateTime);
				}
				
				$bIsValid = true;
				$aIntersection = $this->getIntersection($i);
				foreach ($aIntersection as $oItem)
				{
					if (!$oItem->isValid($dReferenceDateTime))
					{
						$bIsValid = false;
					}
				}
				
				if (!$bIsValid)
				{
					$i++;
				}
			}
			
			$dNextTime = $this->toDateTime($aIntersection, $dReferenceDateTime);
			if ($dDateTime <= $dNextTime)
			{
				return $dNextTime;
			}
			
			$i++;
		}
	}
	
	public function checkTrigger(\DateTime $dDateTime = null) : bool
	{
		throw new Exception\NotImplementedException("Trigger checking not implemented yet.");
	}
	
	public function getIntersection(int $iItem) : array
	{
		if ($this->Items == null)
		{
			$this->populateItems();
		}
		
		$aIntersection = [];
		
		foreach (array_keys($this->Items) as $iRuleId => $sRuleKey)
		{
			$aIntersection[$sRuleKey] = $this->recursiveCalculateItem($iItem, $iRuleId);
		}
		
		return $aIntersection;
	}
	
	protected function getRuleKey(int $iRule) : string
	{
		return array_keys($this->Items)[$iRule];
	}
	
	protected function countItems(int $iRule): int
	{
		return count($this->Items[$this->getRuleKey($iRule)]);
	}
	
	protected function lookupItem(int $iItem, int $iRule) : Item
	{
		return $this->Items[$this->getRuleKey($iRule)][$iItem];
	}
	
	protected function recursiveCalculateItem(int $iItem, int $iRule, int $iCurrent = 0) : Item
	{
		if ($iCurrent == $iRule)
		{
			return $this->lookupItem($iItem % $this->countItems($iCurrent), $iRule);
		}
		
		return $this->recursiveCalculateItem((int) ($iItem / $this->countItems($iCurrent)), $iRule, $iCurrent + 1);
	}
	
	protected function populateItems()
	{
		$this->sortRules();
		
		$this->Items = [];
		
		foreach ($this->Rules as $sRuleName => $oRule)
		{
			$this->Items[$sRuleName] = $oRule->getItems();
		}
		
		$this->Intersections = [];
	}
	
	protected function sortRules()
	{
		
	}
	
	protected function toDateTime(array $aTemporalIntersection, \DateTime $dDateTime = null) : \DateTime
	{
		if ($dDateTime == null)
		{
			$dNewDate = new \DateTime();
		}
		else
		{
			$dNewDate = clone $dDateTime;
		}
		
		foreach ($aTemporalIntersection as $oItem)
		{
			$dNewDate = $oItem->modifyDate($dNewDate);
		}
		
		return $dNewDate;
	}
	
	protected function getPeriodicity() : int
	{
		$iPeriod = 1;
		
		foreach ($this->Rules as $oRule)
		{
			$iPeriod = $iPeriod * $oRule->count();
		}
		
		return $iPeriod;
	}
	
	protected function getHighestContainer(): TemporalInterface
	{
		$iHighestIndex = 0;
		$oHighest = new Temporal\Second;
		
		foreach ($this->Rules as $oRule)
		{
			if ($this->TemporalPrecedence[$oRule->Container::getName()] > $iHighestIndex)
			{
				$iHighestIndex = $this->TemporalPrecedence[$oRule->Container::getName()];
				$oHighest = $oRule->Container;
			}
		}
		
		return $oHighest;
	}
	
	protected function resetReferenceDate(\DateTime $dReferenceDate) : \DateTime
	{
		$oHighestContainer = $this->getHighestContainer();
		
		foreach ($this->TemporalPrecedence as $sTemporalUnit => $iPrecedence)
		{
			if ($sTemporalUnit == $oHighestContainer::getName())
			{
				return $dReferenceDate;
			}
			
			$sTemporalClass = "NxSys\\Library\\Utility\\Scheduler\\Temporal\\".$sTemporalUnit;
			$dReferenceDate = $sTemporalClass::resetDate($dReferenceDate);
		}
	}
}