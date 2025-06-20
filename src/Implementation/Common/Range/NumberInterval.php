<?php

namespace Walnut\Lang\Implementation\Common\Range;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\InvalidNumberInterval;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberInterval as NumberIntervalInterface;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final readonly class NumberInterval implements NumberIntervalInterface {
	public function __construct(
		public MinusInfinity|NumberIntervalEndpointInterface $start,
		public PlusInfinity|NumberIntervalEndpointInterface $end
	) {
		if ($start instanceof NumberIntervalEndpointInterface &&
			$end instanceof NumberIntervalEndpointInterface
		) {
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

	public function containsInterval(NumberIntervalInterface $other): bool {
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
				$this->start !== PlusInfinity::value &&
				$this->end->value == $other->end->value && !$this->end->inclusive && !$other->end->inclusive
			)) {
				return false;
			}
		}
		return true;
	}

	private static function checkIntersectionHelper(self $i1, self $i2, bool $asIntegerInterval): bool {
		if (!$i1->end instanceof NumberIntervalEndpoint) {
			return true;
		}
		if (!$i2->start instanceof NumberIntervalEndpoint) {
			return true;
		}
		$e = $i1->end->value;
		$s = $i2->start->value;
		if ($asIntegerInterval) {
			if ($e !== MinusInfinity::value) {
				$e = $e->floor();
			}
			if ($s !== PlusInfinity::value) {
				$s = $s->ceil();
			}
		}
		return !(
			($e < $s || ($e == $s && !($i1->end->inclusive && $i2->start->inclusive)))
		);
	}

	public function intersectsWith(NumberIntervalInterface $other, bool $asIntegerInterval = false): bool {
		return self::checkIntersectionHelper($this, $other, $asIntegerInterval) &&
			self::checkIntersectionHelper($other, $this, $asIntegerInterval);
	}

	public function intersectionWith(NumberIntervalInterface $other, bool $asIntegerInterval): NumberInterval|null {
		return self::checkIntersectionHelper($this, $other, $asIntegerInterval) &&
			self::checkIntersectionHelper($other, $this, $asIntegerInterval) ?
			new self(
				self::maxStart($this->start, $other->start),
				self::minEnd($this->end, $other->end)
			) : null;
	}

	private static function checkUnionHelper(self $i1, self $i2, bool $asIntegerInterval): bool {
		if (!$i1->end instanceof NumberIntervalEndpoint) {
			return true;
		}
		if (!$i2->start instanceof NumberIntervalEndpoint) {
			return true;
		}
		$e = $i1->end->value;
		$s = $i2->start->value;
		if ($asIntegerInterval) {
			if ($e !== MinusInfinity::value) {
				$e = $e->floor() + ($i1->end->inclusive && $i2->start->inclusive ? 1 : 0);
			}
			if ($s !== PlusInfinity::value) {
				$s = $s->ceil();
			}
		}
		return !(
			($e < $s || ($e == $s && !$i1->end->inclusive && !$i2->start->inclusive))
		);
	}

	public function unitesWith(NumberIntervalInterface $other, bool $asIntegerInterval = false): bool {
		return self::checkUnionHelper($this, $other, $asIntegerInterval) &&
			self::checkUnionHelper($other, $this, $asIntegerInterval);
	}

	public function unionWith(NumberIntervalInterface $other, bool $asIntegerInterval): NumberInterval|null {
		return self::checkUnionHelper($this, $other, $asIntegerInterval) &&
			self::checkUnionHelper($other, $this, $asIntegerInterval) ?
			new self(
				self::minStart($this->start, $other->start),
				self::maxEnd($this->end, $other->end)
			) : null;
	}

	public static function minStart(
		MinusInfinity|NumberIntervalEndpointInterface $start1,
		MinusInfinity|NumberIntervalEndpointInterface $start2
	): MinusInfinity|NumberIntervalEndpointInterface {
		return match(true) {
			$start1 instanceof MinusInfinity, $start2 instanceof MinusInfinity => MinusInfinity::value,
			$start1->value < $start2->value => $start1, $start1->value > $start2->value => $start2,
			default => new NumberIntervalEndpoint($start1->value, $start1->inclusive || $start2->inclusive),
		};
	}

	public static function maxStart(
		MinusInfinity|NumberIntervalEndpointInterface $start1,
		MinusInfinity|NumberIntervalEndpointInterface $start2
	): MinusInfinity|NumberIntervalEndpointInterface {
		return match(true) {
			$start1 instanceof MinusInfinity => $start2, $start2 instanceof MinusInfinity => $start1,
			$start1->value > $start2->value => $start1, $start1->value < $start2->value => $start2,
			default => new NumberIntervalEndpoint($start1->value, $start1->inclusive || $start2->inclusive),
		};
	}

	public static function minEnd(
		PlusInfinity|NumberIntervalEndpointInterface $end1,
		PlusInfinity|NumberIntervalEndpointInterface $end2
	): PlusInfinity|NumberIntervalEndpointInterface {
		return match(true) {
			$end1 instanceof PlusInfinity => $end2, $end2 instanceof PlusInfinity => $end1,
			$end1->value < $end2->value => $end1, $end1->value > $end2->value => $end2,
			default => new NumberIntervalEndpoint($end1->value, $end1->inclusive || $end2->inclusive),
		};
	}

	public static function maxEnd(
		PlusInfinity|NumberIntervalEndpointInterface $end1,
		PlusInfinity|NumberIntervalEndpointInterface $end2
	): PlusInfinity|NumberIntervalEndpointInterface {
		return match(true) {
			$end1 instanceof PlusInfinity, $end2 instanceof PlusInfinity => PlusInfinity::value,
			$end1->value > $end2->value => $end1, $end1->value < $end2->value => $end2,
			default => new NumberIntervalEndpoint($end1->value, $end1->inclusive || $end2->inclusive),
		};
	}

	public function __toString(): string {
		if (
			$this->start instanceof NumberIntervalEndpointInterface &&
			$this->end instanceof NumberIntervalEndpointInterface &&
			$this->start->value == $this->end->value &&
			$this->start->inclusive &&
			$this->end->inclusive
		) {
			return $this->start->value;
		}
		return
			($this->start instanceof NumberIntervalEndpointInterface && $this->start->inclusive ? '[' : '(') .
			($this->start instanceof NumberIntervalEndpointInterface ? $this->start->value : '-Infinity') .
			'..' .
			($this->end instanceof NumberIntervalEndpointInterface ? $this->end->value : '+Infinity') .
			($this->end instanceof NumberIntervalEndpointInterface && $this->end->inclusive ? ']' : ')');
	}
}