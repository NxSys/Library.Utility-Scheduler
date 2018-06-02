<?php

namespace NxSys\Library\Utility\Scheduler;

class Schedule
{
	protected $Rules;
	
	/**
	 * @param array $aRules An array of Rule objects defining the schedule.
	 * @param \DateTime $dDate Reference DateTime. Needed to determine variable Temporal Containers, such as Months.
	 */
	public function __construct(array $aRules = [], \DateTime $dDate = null)
	{
		$this->Rules = [];
		
		foreach ($aRules as $oRule)
		{
			$this->addRule($oRule);
		}
		
		$this->Date = $dDate;
		if ($this->Date == null)
		{
			$this->Date = new \DateTime();
		}
		
		$this->Intersections = null;
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
		
	}
	
	public function getNextOccurrence(\DateTime $dDateTime = null) : \DateTime
	{
		
	}
	
	public function checkTrigger(\DateTime $dDateTime = null) : bool
	{
	
	}
	
	protected function getIntersection(int $iItem) : array
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
			$this->Items[$sRulesName] = $oRule->getItems();
		}
		
		$this->Intersections = [];
		
		
	}
	
	protected function sortRules()
	{
		
	}
	
	protected function toDateTime(array $aTemporalIntersection) : \DateTime
	{
		
	}
}