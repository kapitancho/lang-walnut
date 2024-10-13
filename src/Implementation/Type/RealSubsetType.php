<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\RealSubsetType as RealSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Range\RealRange;

final readonly class RealSubsetType implements RealSubsetTypeInterface, JsonSerializable {

	private RealRange $range;

    /** @param list<RealValue> $subsetValues */
    public function __construct(
        private array $subsetValues
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof RealTypeInterface =>
                self::isInRange($this->subsetValues, $ofType->range()),
            $ofType instanceof RealSubsetTypeInterface =>
                self::isSubset($this->subsetValues, $ofType->subsetValues()),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	/** @param list<RealValue> $subsetValues */
    private static function isInRange(array $subsetValues, RealRange $range): bool {
        foreach($subsetValues as $value) {
            if (!$range->contains($value)) {
                return false;
            }
        }
        return true;
    }

    private static function isSubset(array $subset, array $superset): bool {
        foreach($subset as $value) {
            if (!in_array($value, $superset)) {
                return false;
            }
        }
        return true;
    }

    /** @return list<RealValue> */
    public function subsetValues(): array {
        return $this->subsetValues;
    }

	public function contains(RealValue $value): bool {
		return in_array($value, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("Real[%s]", implode(', ', $this->subsetValues));
	}


	private function minValue(): float {
		return min(array_map(
			static fn(RealValue $value) =>
				$value->literalValue(), $this->subsetValues
		));
	}

	private function maxValue(): float {
		return max(array_map(
			static fn(RealValue $value) =>
				$value->literalValue(), $this->subsetValues
		));
	}

	public function range(): RealRange {
		return $this->range ??= new RealRange(
			$this->minValue(), $this->maxValue()
		);
	}


	public function jsonSerialize(): array {
		return ['type' => 'RealSubset', 'values' => $this->subsetValues];
	}
}