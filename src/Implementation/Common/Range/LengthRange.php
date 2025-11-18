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

	private function min(
		PlusInfinity|Number $value1,
		PlusInfinity|Number $value2
	): PlusInfinity|Number {
		return match(true) {
			$value1 === PlusInfinity::value => $value2,
			$value2 === PlusInfinity::value => $value1,
			default => min($value1, $value2)
		};
	}

	private function max(
		PlusInfinity|Number $value1,
		PlusInfinity|Number $value2
	): PlusInfinity|Number {
		return match(true) {
			$value1 === PlusInfinity::value || $value2 === PlusInfinity::value => PlusInfinity::value,
			default => max($value1, $value2)
		};
	}

	public function intersectsWith(LengthRangeInterface $range): bool {
		if ($this->maxLength instanceof Number && $this->maxLength < $range->minLength) {
			return false;
		}
		if ($range->maxLength instanceof Number && $this->minLength > $range->maxLength) {
			return false;
		}
		return true;
	}

	public function tryRangeIntersectionWith(LengthRangeInterface $range): LengthRangeInterface|null {
		return $this->intersectsWith($range) ? new self (
			$this->max($this->minLength, $range->minLength),
			$this->min($this->maxLength, $range->maxLength)
		) : null;
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

	/** @return -1|0|1 */
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