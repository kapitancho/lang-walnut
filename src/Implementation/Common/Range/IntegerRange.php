<?php

namespace Walnut\Lang\Implementation\Common\Range;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\IntegerRange as IntegerRangeInterface;
use Walnut\Lang\Blueprint\Common\Range\InvalidIntegerRange;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final readonly class IntegerRange implements IntegerRangeInterface, JsonSerializable {
	public function __construct(
		public Number|MinusInfinity $minValue,
		public Number|PlusInfinity  $maxValue
	) {
		if ($this->minValue instanceof Number && $this->maxValue instanceof Number && $maxValue < $minValue) {
			throw new InvalidIntegerRange($minValue, $maxValue);
		}
	}

	public function isSubRangeOf(IntegerRangeInterface $range): bool {
		return
			$this->compare($this->minValue, $range->minValue) > -1 &&
			$this->compare($this->maxValue, $range->maxValue) < 1;
	}

	private function min(
		MinusInfinity|PlusInfinity|Number $value1,
		MinusInfinity|PlusInfinity|Number $value2
	): MinusInfinity|PlusInfinity|Number {
		return match(true) {
			$value1 === MinusInfinity::value || $value2 === MinusInfinity::value => MinusInfinity::value,
			$value1 === PlusInfinity::value => $value2,
			$value2 === PlusInfinity::value => $value1,
			default => min($value1, $value2)
		};
	}

	private function max(
		MinusInfinity|PlusInfinity|Number $value1,
		MinusInfinity|PlusInfinity|Number $value2
	): MinusInfinity|PlusInfinity|Number {
		return match(true) {
			$value1 === PlusInfinity::value || $value2 === PlusInfinity::value => PlusInfinity::value,
			$value1 === MinusInfinity::value => $value2,
			$value2 === MinusInfinity::value => $value1,
			default => max($value1, $value2)
		};
	}

	public function intersectsWith(IntegerRangeInterface $range): bool {
		if ($this->maxValue instanceof Number && $range->minValue instanceof Number && $this->maxValue < $range->minValue) {
			return false;
		}
		if ($this->minValue instanceof Number && $range->maxValue instanceof Number && $this->minValue > $range->maxValue) {
			return false;
		}
		return true;
	}

	public function tryRangeUnionWith(IntegerRangeInterface $range): IntegerRange|null {
		return $this->intersectsWith($range) ? new self (
			$this->min($this->minValue, $range->minValue),
			$this->max($this->maxValue, $range->maxValue)
		) : null;
	}

	public function tryRangeIntersectionWith(IntegerRangeInterface $range): IntegerRange|null {
		return $this->intersectsWith($range) ? new self (
			$this->max($this->minValue, $range->minValue),
			$this->min($this->maxValue, $range->maxValue)
		) : null;
	}

	/** @return int<-1>|int<0>|int<1> */
	private function compare(Number|MinusInfinity|PlusInfinity $a, Number|MinusInfinity|PlusInfinity $b): int {
		if ($a === $b) { return 0; }
		if ($a instanceof MinusInfinity || $b instanceof PlusInfinity) { return -1; }
		if ($a instanceof PlusInfinity || $b instanceof MinusInfinity) { return 1; }
		return $a <=> $b;
	}

    public function asRealRange(): RealRange {
        return new RealRange($this->minValue, $this->maxValue);
    }

    public function contains(Number $value): bool {
        return $this->compare($this->minValue, $value) < 1 &&
            $this->compare($this->maxValue, $value) > -1;
    }

	public function __toString(): string {
		return
			($this->minValue === MinusInfinity::value ? '' : $this->minValue) . '..' .
			($this->maxValue === PlusInfinity::value ? '' : $this->maxValue);
	}

	public function jsonSerialize(): array {
		return [
			'minValue' => $this->minValue instanceof MinusInfinity ? '-Infinity' : (int)(string)$this->minValue,
			'maxValue' => $this->maxValue instanceof PlusInfinity ? '+Infinity' : (int)(string)$this->maxValue
		];
	}
}