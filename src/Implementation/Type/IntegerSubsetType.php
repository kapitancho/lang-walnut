<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Range\IntegerRange as IntegerRangeInterface;
use Walnut\Lang\Blueprint\Range\RealRange;
use Walnut\Lang\Blueprint\Type\IntegerType as IntegerTypeInterface;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType as IntegerSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Blueprint\Type\RealSubsetType as RealSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Implementation\Range\IntegerRange;

final readonly class IntegerSubsetType implements IntegerSubsetTypeInterface, JsonSerializable {

	private IntegerRangeInterface $range;

    /** @param list<IntegerValue> $subsetValues */
    public function __construct(
        private array $subsetValues
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof RealTypeInterface =>
                self::isInRange($this->subsetValues, $ofType->range()),
	        $ofType instanceof RealSubsetTypeInterface =>
             self::isSubsetReal($this->subsetValues, $ofType->subsetValues()),
            $ofType instanceof IntegerTypeInterface =>
                self::isInRange($this->subsetValues, $ofType->range()),
            $ofType instanceof IntegerSubsetTypeInterface =>
                self::isSubset($this->subsetValues, $ofType->subsetValues()),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	/** @param list<IntegerValue> $subsetValues */
    private static function isInRange(array $subsetValues, IntegerRangeInterface|RealRange $range): bool {
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

	/**
	 * @param list<IntegerValue> $subset
	 * @param list<IntegerValue> $superset
	 */
    private static function isSubsetReal(array $subset, array $superset): bool {
        foreach($subset as $value) {
            if (!in_array($value->asRealValue(), $superset)) {
                return false;
            }
        }
        return true;
    }

    /** @return list<IntegerValue> */
    public function subsetValues(): array {
        return $this->subsetValues;
    }

	public function contains(IntegerValue $value): bool {
		return in_array($value, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("Integer[%s]", implode(', ', $this->subsetValues));
	}

	private function minValue(): int {
		return min(array_map(
			static fn(IntegerValue $value) =>
				$value->literalValue(), $this->subsetValues
		));
	}

	private function maxValue(): int {
		return max(array_map(
			static fn(IntegerValue $value) =>
				$value->literalValue(), $this->subsetValues
		));
	}

	public function range(): IntegerRangeInterface {
		return $this->range ??= new IntegerRange(
			$this->minValue(),
			$this->maxValue()
		);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'IntegerSubset',
			'values' => $this->subsetValues
		];
	}
}