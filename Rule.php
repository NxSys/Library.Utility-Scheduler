<?php

namespace NxSys\Library\Utility\Scheduler;

class Rule
{
	public $Container;
	public $Recurrence;
	public $Interval;
	public $Start;
	public $End;
	public $Set;
	
	/**
	 * Represents a rule in a schedule.
	 * @param TemporalInterface $oRecurrence A Temporal Object representing the recurring Temporal unit, e.g., Minute for Minutes in an Hour.
	 * @param TemporalInterface $oContainer A Temporal Object representing the containing Temporal unit, e.g., Month for Days in a Month.
	 * @param int $iInterval How often the rule triggers, e.g., 15 for every 15 Minutes in an Hour.
	 * @param int $iStart 0-indexed initial position. E.g., 4 for starting on the 5th of a Month, 30 for starting at 30 minutes in an hour.
	 * @param int $iEnd 0-indexed ending position.
	 * @param Set A set of explicit items to include. If given, interval/start/end are ignored.
	 * @throws Exception\InvalidRuleException
	 */
	public function __construct(TemporalInterface $oRecurrence,
								TemporalInterface $oContainer,
								int $iInterval = 1,
								int $iStart = 0,
								int $iEnd = null,
								Set $oSet = null)
	{
		$this->Container = $oContainer;
		$this->Recurrence = $oRecurrence;
		
		if (!$this->Container::contains($this->Recurrence))
		{
			throw new Exception\InvalidRuleException("Temporal Containers must bound the specified Temporal Recurrence. You cannot have a Rule that triggers on Hours in a Minute, or Days in a Day.");
		}
		
		if ($iInterval == 0 && $oSet == null)
		{
			throw new Exception\InvalidRuleException("Intervals of 0 cause infinite loops.");
		}
		
		$this->Interval = $iInterval;
		$this->Start = $iStart;
		$this->End = $iEnd;
		
		if ($this->End == null)
		{
			$this->End = $this->Container::count($this->Recurrence);
		}
		$this->Set = $oSet;
	}
	
	public function getItems() : array
	{
		if ($this->hasSet())
		{
			return $this->Set->getItems();
		}
		else
		{
			return $this->Container::getItems($this->Recurrence, $this->Interval, $this->Start, $this->End);
		}
	}
	
	public function count() : int
	{
		if ($this->hasSet())
		{
			return $this->Set->count();
		}
		else
		{
			return count($this->getItems());
		}
	}
	
	public function getName() : string
	{
		return $this->Recurrence::getName();
	}
	
	public function hasSet() : bool
	{
		return $this->Set !== null;
	}
	
	public function createSet($iValues = []) : self
	{
		$this->Set = new Set($this->Recurrence, $this->Container, $iValues);
		return $this;
	}
	
	public function addItem($iValue) : self
	{
		if (!$this->hasSet())
		{
			$this->createSet();
		}
		$this->Set->addItem($iValue);
		return $this;
	}
	
	
	public function reduce(Rule $oRule)
	{
		if (($this->Container::getName() != $oRule->Container::getName()) || ($this->Recurrence::getName() != $oRule->Recurrence::getName()))
		{
			if ($this->Recurrence::getName() == $oRule->Recurrence::getName())
			{
				throw new Exception\RuleConflictException("Cannot have multiple Rules that have differing Temporal Containers but the same Temporal Recurrence, i.e., a Rule that triggers on Seconds of a Minute and Seconds of an Hour. There may be cases where it would be beneficial, for example triggering on the 15th of a Month only if it's a Saturday (Days/Weeks + Days/Month), but it is currently not possible.");
			}
			
			throw new Exception\RuleConflictException("Can only reduce Rules that share Temporal bounds. There should be no need to reduce Rules with differing Temporal bounds, simply add both Rules to the Schedule.");
		}
		
		if ($this->hasSet() or $oRule->hasSet())
		{
			throw new Exception\RuleConflictException("Unable to automatically reduce Rules with Set Topology. Craft a unified rule instead.");
		}
		
		if ($this->Interval < 0 || $oRule->Interval < 0)
		{
			throw new Exception\RuleConflictException("Unable to automatically reduce Rules using negative Intervals.");
		}
		
		//We shouldn't modify the Rule we were passed.
		//Nominally, it shouldn't matter, as once the Rule has been merged into this one it'll likely just be thrown away, but it's bad form none the less.
		$iOtherStart = $oRule->Start;
		$iOtherEnd = $oRule->End;
		
		if (($this->Start >= 0 && $oRule->Start < 0) || ($this->Start < 0 && $oRule->Start >= 0) || ($this->End >= 0 && $oRule->End < 0) || ($this->End < 0 && $oRule->End >= 0))
		{
			throw new Exception\NotImplementedException("Negative bounds not yet implemented.");
			//Convert all Starts/Ends to positive directionalityy.
			//At this point all checks are complete, so modification of this Rule is fine. (Leave secondary Rule as is.)
			
			if ($this->Start < 0)
			{
				$this->Start += $this->Container::count($this->Recurrence);
			}
			
			if ($this->End < 0)
			{
				$this->End += $this->Container::count($this->Recurrence);
			}
			
			if ($oRule->Start < 0)
			{
				$iOtherStart = $oRule->Start + $oRule->Container::count($oRule->Recurrence);
			}
			
			if ($oRule->End < 0)
			{
				$iOtherEnd = $oRule->End + $oRule->Container::count($oRule->Recurrence);
			}
		}
		
		if ($iOtherStart > $this->Start)
		{
			//Always start at the latest specified.
			$this->Start = $iOtherStart;
		}
		
		if ($iOtherEnd < $this->End)
		{
			//Always end at the earliest specified.
			$this->End = $iOtherEnd;
		}
		
		//Combine the intervals using the greatest common denominator.
		$this->Interval = ($this->Interval * $oRule->Interval) / $this::gcd($this->Interval, $oRule->Interval);
	}
	
	protected static function gcd(int $a, int $b) : int
	{
		return ($a % $b) ? $this::gcd($b,$a % $b) : $b;
	}
}