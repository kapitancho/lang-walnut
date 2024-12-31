<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
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

final class IntegerSubsetType implements IntegerSubsetTypeInterface, JsonSerializable {

	private readonly IntegerRangeInterface $actualRange;

    /** @param list<IntegerValue> $subsetValues */
    public function __construct(
        public readonly array $subsetValues
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof RealTypeInterface =>
                self::isInRange($this->subsetValues, $ofType->range),
	        $ofType instanceof RealSubsetTypeInterface =>
             self::isSubsetReal($this->subsetValues, $ofType->subsetValues),
            $ofType instanceof IntegerTypeInterface =>
                self::isInRange($this->subsetValues, $ofType->range),
            $ofType instanceof IntegerSubsetTypeInterface =>
                self::isSubset($this->subsetValues, $ofType->subsetValues),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	/** @param list<IntegerValue> $subsetValues */
    private static function isInRange(array $subsetValues, IntegerRangeInterface|RealRange $range): bool {
	    return array_all($subsetValues, fn($value) => $range->contains($value));
    }

    private static function isSubset(array $subset, array $superset): bool {
	    return array_all($subset, fn($value) => in_array($value, $superset));
    }

	/**
	 * @param list<IntegerValue> $subset
	 * @param list<IntegerValue> $superset
	 */
    private static function isSubsetReal(array $subset, array $superset): bool {
	    return array_all($subset, fn($value) => in_array($value->asRealValue(), $superset));
    }

	public function contains(IntegerValue $value): bool {
		return in_array($value, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("Integer[%s]", implode(', ', $this->subsetValues));
	}

	private function minValue(): Number {
		return min(array_map(
			static fn(IntegerValue $value) =>
				$value->literalValue, $this->subsetValues
		));
	}

	private function maxValue(): Number {
		return max(array_map(
			static fn(IntegerValue $value) =>
				$value->literalValue, $this->subsetValues
		));
	}

	public IntegerRange $range {
		get => $this->actualRange ??= new IntegerRange(
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