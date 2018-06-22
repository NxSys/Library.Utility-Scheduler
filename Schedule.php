<?php

namespace NxSys\Library\Utility\Scheduler;

class Schedule
{
	protected $Rules;
	protected $Items;
	protected $Intersections;
	
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
		
		$this->TemporalPrecedence = ["Second" => 0,
									 "Minute" => 1,
									 "Hour" => 2,
									 "Day" => 3,
									 //"Week" => 4,
									 "Month" => 5,
									 "Year" => 6];
		
	}
	
	/**
	 * Adds a rule to the Schedule.
	 * @param Rule $oRule
	 */
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
			$this->sortRules();
		}
	}
	
	/**
	 * Gets the next N occurrences of the Schedule that occur after the specified DateTime.
	 * @param int $iAmount Number of occurrences to lookup.
	 * @param \DateTime $dDateTime DateTime to get occurrences from. The first occurrence will be the earliest that is after (or exactly equal to) the specified DateTime. If no DateTime is provided, the current time is used.
	 * @return \DateTime[] Array of DateTimes
	 */
	public function getOccurrences(int $iAmount, \DateTime $dDateTime = null) : array
	{
		$aOccurrences = [];
		
		if ($dDateTime == null)
		{
			$dDateTime = new \DateTime();
		}
		
		$dNextDate = $dDateTime;
		
		$iCounter = 0;
		for ($iOccurrences = 0;$iOccurrences < $iAmount;$iOccurrences++)
		{
			$dNextDate = $this->getNextOccurrence($dNextDate, $iCounter); //Counter is passed by reference
			$aOccurrences[] = $dNextDate;
			$dNextDate = clone $dNextDate;
			$dNextDate = Temporal\Second::incrementDate($dNextDate);
		}
		
		return $aOccurrences;
	}
	
	/**
	 * Gets the next occurrence of the Schedule that is after the specified DateTime.
	 * NOTE: This function has a second parameter, which is modified by reference. It is used as a counter to track the Schedule's position in the Intersection list. This greatly increases performance when retrieving successive occurrences in Schedules with a large number of Intersections, but if used incorrectly it can result in occurrences being skipped. It is used internally by getOccurrences(), and shouldn't be used by external callers.
	 * @param \DateTime $dDateTime DateTime to get occurrences from. The returned occurrence will be the earliest that is after (or exactly equal to) the specified DateTime. If no DateTime is provided, the current time is used.
	 * @return \DateTime
	 */
	public function getNextOccurrence(\DateTime $dDateTime = null, &$i = 0) : \DateTime
	{
		if ($dDateTime == null)
		{
			$dDateTime = new \DateTime();
		}
		
		$dReferenceDateTime = clone $dDateTime;
		$dReferenceDateTime = $this->resetReferenceDate($dReferenceDateTime);
		
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
					$i = 0;
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
			
			$i++;
			
			$dNextTime = $this->toDateTime($aIntersection, $dReferenceDateTime);
			if ($dDateTime <= $dNextTime)
			{
				return $dNextTime;
			}
		}
	}
	
	public function checkTrigger(\DateTime $dDateTime = null) : bool
	{
		throw new Exception\NotImplementedException("Trigger checking not implemented yet.");
	}
	
	protected function getIntersection(int $iItem) : array
	{
		if ($this->Items == null)
		{
			$this->populateItems();
		}
		
		if (array_key_exists($iItem, $this->Intersections))
		{
			return $this->Intersections[$iItem];
		}
		
		$aIntersection = [];
		
		foreach (array_keys($this->Items) as $iRuleId => $sRuleKey)
		{
			$aIntersection[$sRuleKey] = $this->recursiveCalculateItem($iItem, $iRuleId);
		}
		
		$this->Intersections[$iItem] = $aIntersection;
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
		$this->Items = [];
		
		foreach ($this->Rules as $sRuleName => $oRule)
		{
			$this->Items[$sRuleName] = $oRule->getItems();
		}
		
		$this->Intersections = [];
	}
	
	protected function sortRules()
	{
		$aPrecedence = $this->TemporalPrecedence;
		$cPrecedenceCompare = function ($a, $b) use ($aPrecedence) {return $aPrecedence[$a->getName()] - $aPrecedence[$b->getName()];};
		uasort($this->Rules, $cPrecedenceCompare); //Sort while maintaining associative keys.
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
	
	public function getHighestContainer(): TemporalInterface
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