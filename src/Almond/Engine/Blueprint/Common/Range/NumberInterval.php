<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common\Range;

use BcMath\Number;
use Stringable;

final readonly class NumberInterval implements Stringable {
	public function __construct(
		public MinusInfinity|NumberIntervalEndpoint $start,
		public PlusInfinity|NumberIntervalEndpoint $end
	) {
		if ($start instanceof NumberIntervalEndpoint && $end instanceof NumberIntervalEndpoint) {
			if ($start->value > $end->value || (
				$start->value == $end->value && (!$start->inclusive || !$end->inclusive)
			)) {
				throw new InvalidNumberInterval($start, $end);
			}
		}
	}

	public static function singleNumber(Number $value): self {
		return new self(
			new NumberIntervalEndpoint($value, true),
			new NumberIntervalEndpoint($value, true)
		);
	}

	public function contains(Number $value): bool {
		if ($this->start instanceof NumberIntervalEndpoint) {
			if ($value < $this->start->value || (
					$value == $this->start->value && !$this->start->inclusive
				)) {
				return false;
			}
		}
		if ($this->end instanceof NumberIntervalEndpoint) {
			if ($value > $this->end->value || (
					$value == $this->end->value && !$this->end->inclusive
				)) {
				return false;
			}
		}
		return true;
	}

	public function containsInterval(NumberInterval $other): bool {
		if ($other->start instanceof MinusInfinity) {
			if (!$this->start instanceof MinusInfinity) {
				return false;
			}
		} else {
			if (!$this->contains($other->start->value) && !(
					$this->start !== MinusInfinity::value &&
					$this->start->value == $other->start->value && !$this->start->inclusive && !$other->start->inclusive
				)) {
				return false;
			}
		}
		if ($other->end instanceof PlusInfinity) {
			if (!$this->end instanceof PlusInfinity) {
				return false;
			}
		} else {
			if (!$this->contains($other->end->value) && !(
					$this->end !== PlusInfinity::value &&
					$this->end->value == $other->end->value && !$this->end->inclusive && !$other->end->inclusive
				)) {
				return false;
			}
		}
		return true;
	}

	private static function checkIntersectionHelper(NumberInterval $i1, NumberInterval $i2, bool $asIntegerInterval): bool {
		if (!$i1->end instanceof NumberIntervalEndpoint) {
			return true;
		}
		if (!$i2->start instanceof NumberIntervalEndpoint) {
			return true;
		}
		$e = $i1->end->value;
		$s = $i2->start->value;
		if ($asIntegerInterval) {
			$e = $e->floor();
			$s = $s->ceil();
		}
		return !(
		($e < $s || ((string)$e === (string)$s && !($i1->end->inclusive && $i2->start->inclusive)))
		);
	}

	public function intersectsWith(NumberInterval $other, bool $asIntegerInterval = false): bool {
		return self::checkIntersectionHelper($this, $other, $asIntegerInterval) &&
			self::checkIntersectionHelper($other, $this, $asIntegerInterval);
	}

	public function intersectionWith(NumberInterval $other, bool $asIntegerInterval): NumberInterval|null {
		return self::checkIntersectionHelper($this, $other, $asIntegerInterval) &&
		self::checkIntersectionHelper($other, $this, $asIntegerInterval) ?
			new self(
				self::maxStart($this->start, $other->start),
				self::minEnd($this->end, $other->end)
			) : null;
	}

	private static function checkUnionHelper(NumberInterval $i1, NumberInterval $i2, bool $asIntegerInterval): bool {
		if (!$i1->end instanceof NumberIntervalEndpoint) {
			return true;
		}
		if (!$i2->start instanceof NumberIntervalEndpoint) {
			return true;
		}
		$e = $i1->end->value;
		$s = $i2->start->value;
		if ($asIntegerInterval) {
			$e = $e->floor()->add($i1->end->inclusive && $i2->start->inclusive ? 1 : 0);
			$s = $s->ceil();
		}
		return !(
		($e < $s || ($e == $s && !$i1->end->inclusive && !$i2->start->inclusive))
		);
	}

	public function unitesWith(NumberInterval $other, bool $asIntegerInterval = false): bool {
		return self::checkUnionHelper($this, $other, $asIntegerInterval) &&
			self::checkUnionHelper($other, $this, $asIntegerInterval);
	}

	public function unionWith(NumberInterval $other, bool $asIntegerInterval): NumberInterval|null {
		return self::checkUnionHelper($this, $other, $asIntegerInterval) &&
		self::checkUnionHelper($other, $this, $asIntegerInterval) ?
			new self(
				self::minStart($this->start, $other->start),
				self::maxEnd($this->end, $other->end)
			) : null;
	}

	public static function minStart(
		MinusInfinity|NumberIntervalEndpoint $start1,
		MinusInfinity|NumberIntervalEndpoint $start2
	): MinusInfinity|NumberIntervalEndpoint {
		return match(true) {
			$start1 instanceof MinusInfinity, $start2 instanceof MinusInfinity => MinusInfinity::value,
			$start1->value < $start2->value => $start1, $start1->value > $start2->value => $start2,
			default => new NumberIntervalEndpoint($start1->value, $start1->inclusive || $start2->inclusive),
		};
	}

	public static function maxStart(
		MinusInfinity|NumberIntervalEndpoint $start1,
		MinusInfinity|NumberIntervalEndpoint $start2
	): MinusInfinity|NumberIntervalEndpoint {
		return match(true) {
			$start1 instanceof MinusInfinity => $start2, $start2 instanceof MinusInfinity => $start1,
			$start1->value > $start2->value => $start1, $start1->value < $start2->value => $start2,
			default => new NumberIntervalEndpoint($start1->value, $start1->inclusive || $start2->inclusive),
		};
	}

	public static function minEnd(
		PlusInfinity|NumberIntervalEndpoint $end1,
		PlusInfinity|NumberIntervalEndpoint $end2
	): PlusInfinity|NumberIntervalEndpoint {
		return match(true) {
			$end1 instanceof PlusInfinity => $end2, $end2 instanceof PlusInfinity => $end1,
			$end1->value < $end2->value => $end1, $end1->value > $end2->value => $end2,
			default => new NumberIntervalEndpoint($end1->value, $end1->inclusive || $end2->inclusive),
		};
	}

	public static function maxEnd(
		PlusInfinity|NumberIntervalEndpoint $end1,
		PlusInfinity|NumberIntervalEndpoint $end2
	): PlusInfinity|NumberIntervalEndpoint {
		return match(true) {
			$end1 instanceof PlusInfinity, $end2 instanceof PlusInfinity => PlusInfinity::value,
			$end1->value > $end2->value => $end1, $end1->value < $end2->value => $end2,
			default => new NumberIntervalEndpoint($end1->value, $end1->inclusive || $end2->inclusive),
		};
	}

	public function __toString(): string {
		if (
			$this->start instanceof NumberIntervalEndpoint &&
			$this->end instanceof NumberIntervalEndpoint &&
			$this->start->value == $this->end->value &&
			$this->start->inclusive &&
			$this->end->inclusive
		) {
			return $this->start->value;
		}
		return
			($this->start instanceof NumberIntervalEndpoint && $this->start->inclusive ? '[' : '(') .
			($this->start instanceof NumberIntervalEndpoint ? $this->start->value : '-Infinity') .
			'..' .
			($this->end instanceof NumberIntervalEndpoint ? $this->end->value : '+Infinity') .
			($this->end instanceof NumberIntervalEndpoint && $this->end->inclusive ? ']' : ')');
	}
}

