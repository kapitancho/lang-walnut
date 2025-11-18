<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\NumberRange as NumberRangeInterface;
use Walnut\Lang\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class RealType implements RealTypeInterface, JsonSerializable {
    public function __construct(
	    public NumberRangeInterface $numberRange,
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof RealTypeInterface => $ofType->numberRange->containsRange($this->numberRange),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function contains(int|float|Number $value): bool {
		if (is_int($value)) {
			$value = new Number($value);
		} elseif (is_float($value)) {
			$value = new Number((string)$value);
		}
		return $this->numberRange->contains($value);
	}

	public function __toString(): string {
		$range = (string)$this->numberRange;
		if (count($this->numberRange->intervals) === 1) {
			$range = preg_replace('#((^\(-Infinity|^\[)(.*?)(\+Infinity\)$|]$))#', '$3', $range);
		}
		$range = preg_replace('#[+-]Infinity#', '', $range);
		return sprintf("Real%s", $range === '..' ? '' : "<$range>");
	}

	public function jsonSerialize(): array {
		return ['type' => 'Real', 'range' => $this->numberRange];
	}
}