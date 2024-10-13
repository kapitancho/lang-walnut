<?php

namespace Walnut\Lang\Implementation\Range;

use JsonSerializable;
use Walnut\Lang\Blueprint\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Range\LengthRange as LengthRangeInterface;
use Walnut\Lang\Blueprint\Range\PlusInfinity;

final readonly class LengthRange implements LengthRangeInterface, JsonSerializable {
	public function __construct(
		public int              $minLength,
		public int|PlusInfinity $maxLength
	) {
		if (
			$this->minLength < 0 || (
				is_int($maxLength) && $maxLength < $minLength
			)
		) {
			throw new InvalidLengthRange($minLength, $maxLength);
		}
	}

    public function minLength(): int {
        return $this->minLength;
    }
    public function maxLength(): int|PlusInfinity {
        return $this->maxLength;
    }

    public function isSubRangeOf(LengthRangeInterface $range): bool {
		return
			$this->compare($this->minLength, $range->minLength) > -1 &&
			$this->compare($this->maxLength, $range->maxLength) < 1;
	}

	public function lengthInRange(int $length): bool {
		return
			$this->compare($this->minLength, $length) < 1 &&
			$this->compare($this->maxLength, $length) > -1;
	}

	/** @return int<-1>|int<0>|int<1> */
	private function compare(int|PlusInfinity $a, int|PlusInfinity $b): int {
		if ($a === $b) { return 0; }
		if ($a instanceof PlusInfinity) { return 1; }
		if ($b instanceof PlusInfinity) { return -1; }
		return $a <=> $b;
	}

	public function __toString(): string {
		return ($this->minLength === 0 ? '' : $this->minLength) . '..' .
			($this->maxLength === PlusInfinity::value ? '' : $this->maxLength);
	}

	public function jsonSerialize(): array {
		return [
			'minLength' => $this->minLength,
			'maxLength' => $this->maxLength instanceof PlusInfinity ? '+Infinity' : $this->maxLength
		];
	}
}