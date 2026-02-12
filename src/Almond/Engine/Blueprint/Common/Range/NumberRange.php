<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common\Range;

use BcMath\Number;
use JsonSerializable;
use Stringable;

final readonly class NumberRange implements Stringable, JsonSerializable {

	/** @var array<NumberInterval> */
	public array $intervals;
	/** @var array<NumberInterval> */
	public array $adjustedIntervals;

	public NumberIntervalEndpoint|MinusInfinity $min;
	public NumberIntervalEndpoint|PlusInfinity $max;

	public function __construct(
		public bool $isIntegerRange,
		NumberInterval ... $intervals
	) {
		if (count($intervals) === 0) {
			throw new InvalidNumberRange;
		}
		$this->intervals = $intervals;

		$this->adjustedIntervals = self::adjustIntervals($isIntegerRange, $intervals);
		$firstValue = $this->adjustedIntervals[array_key_first($this->adjustedIntervals)];
		$lastValue = $this->adjustedIntervals[array_key_last($this->adjustedIntervals)];
		$this->min = $firstValue->start;
		$this->max = $lastValue->end;
	}

	/** @param non-empty-array<NumberInterval> $intervals */
	private static function adjustIntervals(bool $asIntegerRange, array $intervals): array {
		usort($intervals, fn(NumberInterval $a, NumberInterval $b) => match(true) {
			$a->start instanceof MinusInfinity => -1, $b->start instanceof MinusInfinity => 1,
			default => $a->start->value <=> $b->start->value
		});
		$adjusted = [];
		$current = null;
		foreach ($intervals as $interval) {
			if ($current === null) {
				$current = $interval;
			} else {
				$union = $current->unionWith($interval, $asIntegerRange);
				if ($union) {
					$current = $union;
				} else {
					$adjusted[] = $current;
					$current = $interval;
				}
			}
		}
		$adjusted[] = $current;
		return $adjusted;
	}

	public function contains(Number $value): bool {
		return array_any($this->intervals, fn($interval) => $interval->contains($value));
	}

	public function containsInterval(NumberInterval $interval): bool {
		return array_any($this->adjustedIntervals, fn(NumberInterval $existingInterval) =>
			$existingInterval->containsInterval($interval));
	}

	public function containsRange(NumberRange $range): bool {
		return array_all($range->intervals, fn(NumberInterval $interval) =>
			$this->containsInterval($interval));
	}

	public function intersectionWith(NumberRange $other): NumberRange|null {
		$intervals = [];
		foreach ($this->intervals as $interval) {
			foreach ($other->intervals as $otherInterval) {
				$intersection = $interval->intersectionWith($otherInterval, $this->isIntegerRange);
				if ($intersection !== null) {
					$intervals[] = $intersection;
				}
			}
		}
		if (count($intervals) === 0) {
			return null;
		}
		return new self(
			$this->isIntegerRange, ...
			self::adjustIntervals(
				$this->isIntegerRange,
				$intervals
			)
		);
	}

	public function unionWith(NumberRange $other): NumberRange {
		return new self($this->isIntegerRange, ...
			self::adjustIntervals(
				$this->isIntegerRange,
				array_merge($this->intervals, $other->intervals)
			)
		);
	}

	public function __toString(): string {
		return implode(', ', $this->intervals);
	}

	public function jsonSerialize(): array {
		return ['intervals' => $this->intervals];
	}
}
