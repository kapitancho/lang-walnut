<?php

namespace Walnut\Lang\Implementation\Common\Range;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\InvalidRealRange;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Range\RealRange as RealRangeInterface;

final readonly class RealRange implements RealRangeInterface, JsonSerializable {
	public function __construct(
		public Number|MinusInfinity $minValue,
		public Number|PlusInfinity  $maxValue
	) {
		if ($minValue instanceof Number && $maxValue instanceof Number && $maxValue < $minValue) {
			throw new InvalidRealRange($minValue, $maxValue);
		}
	}

    public function isSubRangeOf(RealRangeInterface $range): bool {
		return
			$this->compare($this->minValue, $range->minValue) > -1 &&
			$this->compare($this->maxValue, $range->maxValue) < 1;
	}

	public function contains(Number $value): bool {
		return $this->compare($this->minValue, $value) < 1 &&
			$this->compare($this->maxValue, $value) > -1;
	}

	/** @return int<-1>|int<0>|int<1> */
	private function compare(Number|MinusInfinity|PlusInfinity $a, Number|MinusInfinity|PlusInfinity $b): int {
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
			'minValue' => $this->minValue instanceof MinusInfinity ? '-Infinity' : (float)(string)$this->minValue,
			'maxValue' => $this->maxValue instanceof PlusInfinity ? '+Infinity' : (float)(string)$this->maxValue
		];
	}
}