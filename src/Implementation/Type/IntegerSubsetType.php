<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\IntegerRange as IntegerRangeInterface;
use Walnut\Lang\Blueprint\Common\Range\RealRange;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType as IntegerSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\IntegerType as IntegerTypeInterface;
use Walnut\Lang\Blueprint\Type\RealSubsetType as RealSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Implementation\Common\Range\IntegerRange;

final class IntegerSubsetType implements IntegerSubsetTypeInterface, JsonSerializable {

	private readonly IntegerRangeInterface $actualRange;

    /**
     * @param list<Number> $subsetValues
     * @throws InvalidArgumentException|DuplicateSubsetValue
     */
    public function __construct(
        public readonly array $subsetValues
    ) {
		if ($subsetValues === []) {
			// @codeCoverageIgnoreStart
			throw new InvalidArgumentException("Cannot create an empty subset type");
			// @codeCoverageIgnoreEnd
		}
		$selected = [];
		foreach($subsetValues as $value) {
			if (!$value instanceof Number || ((string)$value !== (string)$value->floor())) {
				// @codeCoverageIgnoreStart
				throw new InvalidArgumentException(
					sprintf("Invalid value: '%s'", $value)
				);
				// @codeCoverageIgnoreEnd
			}
			if (array_key_exists((string)$value, $selected)) {
				DuplicateSubsetValue::ofInteger(
					sprintf("Integer[%s]",
						implode(', ', $subsetValues)),
					$value);
			}
			$selected[(string)$value] = true;
		}
    }

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

	/** @param list<Number> $subsetValues */
    private static function isInRange(array $subsetValues, IntegerRangeInterface|RealRange $range): bool {
	    return array_all($subsetValues, fn($value) => $range->contains($value));
    }

    private static function isSubset(array $subset, array $superset): bool {
	    return array_all($subset, fn($value) => in_array($value, $superset));
    }

	/**
	 * @param list<Number> $subset
	 * @param list<Number> $superset
	 */
    private static function isSubsetReal(array $subset, array $superset): bool {
	    return array_all($subset, fn($value) => in_array($value, $superset));
    }

	public function contains(IntegerValue $value): bool {
		return in_array($value->literalValue, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("Integer[%s]", implode(', ', $this->subsetValues));
	}

	private function minValue(): Number {
		return min(array_map(
			static fn(Number $value) =>
				$value, $this->subsetValues
		));
	}

	private function maxValue(): Number {
		return max(array_map(
			static fn(Number $value) =>
				$value, $this->subsetValues
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
			'values' => array_map(fn(Number $value): int => (int)(string)$value, $this->subsetValues)
		];
	}
}