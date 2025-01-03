<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Blueprint\Type\RealSubsetType as RealSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Common\Range\RealRange;

final class RealSubsetType implements RealSubsetTypeInterface, JsonSerializable {

	private readonly RealRange $actualRange;

    /** @param list<RealValue> $subsetValues */
    public function __construct(
        public readonly array $subsetValues
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof RealTypeInterface =>
                self::isInRange($this->subsetValues, $ofType->range),
            $ofType instanceof RealSubsetTypeInterface =>
                self::isSubset($this->subsetValues, $ofType->subsetValues),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	/** @param list<RealValue> $subsetValues */
    private static function isInRange(array $subsetValues, RealRange $range): bool {
	    return array_all($subsetValues, fn($value) => $range->contains($value));
    }

    private static function isSubset(array $subset, array $superset): bool {
	    return array_all($subset, fn($value) => in_array($value, $superset));
    }

	public function contains(RealValue $value): bool {
		return in_array($value, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("Real[%s]", implode(', ', $this->subsetValues));
	}


	private function minValue(): Number {
		return new Number(min(array_map(
			static fn(RealValue $value) =>
				$value->literalValue, $this->subsetValues
		)));
	}

	private function maxValue(): Number {
		return new Number(max(array_map(
			static fn(RealValue $value) =>
				$value->literalValue, $this->subsetValues
		)));
	}

	public RealRange $range {
		get => $this->actualRange ??= new RealRange(
			$this->minValue(), $this->maxValue()
		);
	}

	public function jsonSerialize(): array {
		return ['type' => 'RealSubset', 'values' => $this->subsetValues];
	}
}