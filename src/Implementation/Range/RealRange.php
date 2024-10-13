<?php

namespace Walnut\Lang\Implementation\Range;

use JsonSerializable;
use Walnut\Lang\Blueprint\Range\InvalidRealRange;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Range\RealRange as RealRangeInterface;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;

final readonly class RealRange implements RealRangeInterface, JsonSerializable {
	public function __construct(
		public float|MinusInfinity $minValue,
		public float|PlusInfinity  $maxValue
	) {
		if (is_float($maxValue) && $maxValue < $minValue) {
			throw new InvalidRealRange($minValue, $maxValue);
		}
	}

    public function minValue(): float|MinusInfinity {
        return $this->minValue;
    }
    public function maxValue(): float|PlusInfinity {
        return $this->maxValue;
    }

    public function isSubRangeOf(RealRangeInterface $range): bool {
		return
			$this->compare($this->minValue, $range->minValue()) > -1 &&
			$this->compare($this->maxValue, $range->maxValue()) < 1;
	}

	public function contains(RealValue|IntegerValue $value): bool {
		return $this->compare($this->minValue, $value->literalValue()) < 1 &&
			$this->compare($this->maxValue, $value->literalValue()) > -1;
	}

	/** @return int<-1>|int<0>|int<1> */
	private function compare(float|MinusInfinity|PlusInfinity $a, float|MinusInfinity|PlusInfinity $b): int {
		if ($a === $b) { return 0; }
		if ($a instanceof MinusInfinity || $b instanceof PlusInfinity) { return -1; }
		if ($a instanceof PlusInfinity || $b instanceof MinusInfinity) { return 1; }
		return $a <=> $b;
	}

	public function __toString(): string {
		return
			($this->minValue === MinusInfinity::value ? '' : $this->minValue) . '..' .
			($this->maxValue === PlusInfinity::value ? '' : $this->maxValue);
	}

	public function jsonSerialize(): array {
		return [
			'minValue' => $this->minValue instanceof MinusInfinity ? '-Infinity' : $this->minValue,
			'maxValue' => $this->maxValue instanceof PlusInfinity ? '+Infinity' : $this->maxValue
		];
	}
}