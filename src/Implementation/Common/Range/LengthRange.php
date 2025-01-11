<?php

namespace Walnut\Lang\Implementation\Common\Range;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Common\Range\LengthRange as LengthRangeInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final readonly class LengthRange implements LengthRangeInterface, JsonSerializable {
	public function __construct(
		public Number              $minLength,
		public Number|PlusInfinity $maxLength
	) {
		if (
			$this->minLength < 0 || (
				$maxLength instanceof Number && $maxLength < $minLength
			)
		) {
			throw new InvalidLengthRange($minLength, $maxLength);
		}
	}

    public function isSubRangeOf(LengthRangeInterface $range): bool {
		return
			$this->compare($this->minLength, $range->minLength) > -1 &&
			$this->compare($this->maxLength, $range->maxLength) < 1;
	}

	public function lengthInRange(Number $length): bool {
		return
			$this->compare($this->minLength, $length) < 1 &&
			$this->compare($this->maxLength, $length) > -1;
	}

	/** @return int<-1>|int<0>|int<1> */
	private function compare(Number|PlusInfinity $a, Number|PlusInfinity $b): int {
		if ($a === $b) { return 0; }
		if ($a instanceof PlusInfinity) { return 1; }
		if ($b instanceof PlusInfinity) { return -1; }
		return $a <=> $b;
	}

	public function __toString(): string {
		return ((int)(string)$this->minLength === 0 ? '' : $this->minLength) . '..' .
			($this->maxLength === PlusInfinity::value ? '' : $this->maxLength);
	}

	public function jsonSerialize(): array {
		return [
			'minLength' => (int)(string)$this->minLength,
			'maxLength' => $this->maxLength instanceof PlusInfinity ? '+Infinity' : (int)(string)$this->maxLength
		];
	}
}