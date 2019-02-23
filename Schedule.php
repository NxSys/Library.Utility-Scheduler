<?php

namespace NxSys\Library\Utility\Scheduler;

class Schedule
{
	protected $Rules;
	protected $Items;
	protected $Intersections;
	protected $TemporalPrecedence;
	protected $LastTrigger;
	protected $NextTrigger;
	
	/**
	 * @param array $aRules An array of Rule objects defining the schedule.
	 */
	public function __construct(array $aRules = [], array $aTemporalPrecedence = null)
	{
		$this->Rules = [];
		
		foreach ($aRules as $oRule)
		{
			$this->addRule($oRule);
		}
		
		$this->Items = null;
		
		if ($aTemporalPrecedence == null)
		{
			$this->TemporalPrecedence = ["NxSys\\Library\\Utility\\Scheduler\\Temporal\\Second" => 0,
										 "NxSys\\Library\\Utility\\Scheduler\\Temporal\\Minute" => 1,
										 "NxSys\\Library\\Utility\\Scheduler\\Temporal\\Hour" => 2,
										 "NxSys\\Library\\Utility\\Scheduler\\Temporal\\Day" => 3,
										 "NxSys\\Library\\Utility\\Scheduler\\Temporal\\Week" => 4,
										 "NxSys\\Library\\Utility\\Scheduler\\Temporal\\Month" => 5,
										 "NxSys\\Library\\Utility\\Scheduler\\Temporal\\Year" => 6];
		}
		else
		{
			$this->TemporalPrecedence = $aTemporalPrecedence;
		}
		
		$this->LastTrigger = new \DateTime();
		$this->NextTrigger = null;
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
		
		$this->NextTrigger = null; //Reset stored next trigger, as we've changed our schedule.
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
	
	/**
	 * Checks if a given DateTime (defaults to the current time) is after the next scheduled occurrence.
	 * Keeps track of last and next triggers. Only needs to recalculate next trigger when a trigger is met, so this method should be performant enough to call repeatedly.
	 * Last trigger is initially set to the time the Schedule object was created, and will thus trigger on the first occurrence after that time.
	 * Use setLastTrigger() to override the setting if necessary.
	 * @param \DateTime $dDateTime DateTime to check trigger for. Will return true if the given DateTime is after (or exactly equal to) the next scheduled ocurrence. Defaults to the current time.
	 * @return bool True if triggered, False otherwise.
	 */
	public function checkTrigger(\DateTime $dDateTime = null) : bool
	{
		if ($this->NextTrigger === null)
		{
			$this->NextTrigger = $this->getNextOccurrence($this->LastTrigger);
		}
		
		if ($dDateTime === null)
		{
			$dDateTime = new \DateTime();
		}
		
		if ($dDateTime >= $this->NextTrigger)
		{
			$this->LastTrigger = Temporal\Second::incrementDate($this->NextTrigger); //Need to increment so we don't keep getting the same trigger.
			$this->NextTrigger = $this->getNextOccurrence($this->LastTrigger);
			return true;
		}
		
		return false;
	}
	
	/**
	* Sets the last triggered time to the specified DateTime, and recalculate Next Trigger accordingly.
	* @param \DateTime $dDateTime DateTime to set as last triggered time.
	*/
	public function setLastTrigger(\DateTime $dDateTime)
	{
		$this->LastTrigger = $dDateTime;
		$this->NextTrigger = $this->getNextOccurrence($this->LastTrigger);
	}
	
	/**
	* Static method to create a schedule based on a cron-like string. Currently only supports Minutes/Hour, Hour/Day, Day/Month, and Month/Year.
	* @param string $sCronString A cron-like string to create a schedule from.
	* @return Schedule
	*/
	static function fromCron($sCronString)
	{
		$oSchedule = new Schedule();
		
		$cronArr = explode(' ', $sCronString);

		$cronPairs = [[new Temporal\Minute, new Temporal\Hour, 0],
						[new Temporal\Hour, new Temporal\Day, 0],
						[new Temporal\Day, new Temporal\Month, -1],
						[new Temporal\Month, new Temporal\Year, -1]];
						

		foreach ($cronArr as $index => $val)
		{
			
			$rule = new Rule($cronPairs[$index][0], $cronPairs[$index][1]);
			if ($val != '*' && stripos($val, '/') === false)
			{
				$rule->createSet(self::cronRangeToSet($val, $cronPairs[$index][2]));
			}
			else
			{
				if (stripos($val, ',') !== false)
				{
					throw new Exception\NotImplementedException("Cron strings do not currently support mixing intervals and sets.");
				}
				
				if (stripos($val, '/') !== false)
				{
					$recurrenceArr = explode("/", $val);
					
					$rule->Interval = (int) $recurrenceArr[1];
				
					if (stripos($recurrenceArr[0], '-') !== false)
					{
						$rangeArr = explode('-', $recurrenceArr[0]);
						$rule->Start = (int) $rangeArr[0] + $cronPairs[$index][2];
						$rule->End = (int) $rangeArr[1] + $cronPairs[$index][2];
					}
				}
			}
			$oSchedule->addRule($rule);
		}
		
		return $oSchedule;
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
		$cPrecedenceCompare = function ($a, $b) use ($aPrecedence) {return $aPrecedence[get_class($a->Recurrence)] - $aPrecedence[get_class($b->Recurrence)];};
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
			if ($this->TemporalPrecedence[get_class($oRule->Container)] > $iHighestIndex)
			{
				$iHighestIndex = $this->TemporalPrecedence[get_class($oRule->Container)];
				$oHighest = $oRule->Container;
			}
		}
		
		return $oHighest;
	}
	
	protected function resetReferenceDate(\DateTime $dReferenceDate) : \DateTime
	{	
		$oHighestContainer = $this->getHighestContainer();
		
		$sLastTemporalUnit = null;
		
		foreach ($this->TemporalPrecedence as $sTemporalUnit => $iPrecedence)
		{
			if ($sLastTemporalUnit != null)
			{
				if ($sTemporalUnit::doReset(new $sLastTemporalUnit))
				{
					$dReferenceDate = $sTemporalUnit::modifyDate(new $sLastTemporalUnit, 0, $dReferenceDate);
				}
			}
			
			if ($sTemporalUnit == get_class($oHighestContainer))
			{	
				return $dReferenceDate;
			}
			
			$sLastTemporalUnit = $sTemporalUnit;
		}
	}
	
	protected static function cronRangeToSet($s, $mod)
	{
		if (stripos($s, '/') !== false)
		{
			throw new Exception\NotImplementedException("Cron strings do not currently support mixing intervals and sets.");
		}
		
		$aMonthNames = ['Jan' => '1', 'Feb' => '2', 'Mar' => '3', 'Apr' => '4', 'May' => '5', 'Jun' => '6', 'Jul' => '7', 'Aug' => '8', 'Sep' => '9', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12'];
		$aDayNames = ['Mon' => '1', 'Tue' => '2', 'Wed' => '3', 'Thu' => '4', 'Fri' => '5', 'Sat' => '6', 'Sun' => '7'];
		$s = str_replace(array_keys($aMonthNames), array_values($aMonthNames), $s);
		$s = str_replace(array_keys($aDayNames), array_values($aDayNames), $s);
		
		$aSet = [];
		
		foreach (explode(',', $s) as $iIndex => $sVal)
		{
			if (stripos($sVal, '-') !== false)
			{
				$aRange = explode('-', $sVal);
				for($c = (int) $aRange[0]; $c <= (int) $aRange[1]; $c++)
				{
					$aSet[] = $c + $mod;
				}
			}
			else
			{
				$aSet[] = (int) $sVal + $mod;
			}
		}
		
		return $aSet;
	}
	
	
}