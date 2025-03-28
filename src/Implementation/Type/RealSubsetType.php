<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\RealSubsetType as RealSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Common\Range\RealRange;

final class RealSubsetType implements RealSubsetTypeInterface, JsonSerializable {

	private readonly RealRange $actualRange;

    /** @param list<Number> $subsetValues */
    public function __construct(
        public readonly array $subsetValues
    ) {
	    if ($subsetValues === []) {
		    throw new InvalidArgumentException("Cannot create an empty subset type");
	    }
	    $selected = [];
	    foreach($subsetValues as $value) {
		    if (!$value instanceof Number) {
			    throw new InvalidArgumentException(
				    sprintf("Invalid value: '%s'", $value)
			    );
		    }
			$vs = (string)$value;
			if (!str_contains($vs, '.')) {
				$vs .= '.';
			}
			$vs = rtrim(rtrim($vs, '0'), '.');
		    if (array_key_exists($vs, $selected)) {
			    DuplicateSubsetValue::ofReal(
				    sprintf("Real[%s]",
					    implode(', ', $subsetValues)),
				    $value);
		    }
		    $selected[$vs] = true;
	    }
    }

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

	/** @param list<Number> $subsetValues */
    private static function isInRange(array $subsetValues, RealRange $range): bool {
	    return array_all($subsetValues, fn($value) => $range->contains($value));
    }

    private static function isSubset(array $subset, array $superset): bool {
	    return array_all($subset, fn($value) => in_array($value, $superset));
    }

	public function contains(RealValue $value): bool {
		return in_array($value->literalValue, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("Real[%s]", implode(', ', $this->subsetValues));
	}


	private function minValue(): Number {
		return new Number(min(array_map(
			static fn(Number $value) =>
				$value, $this->subsetValues
		)));
	}

	private function maxValue(): Number {
		return new Number(max(array_map(
			static fn(Number $value) =>
				$value, $this->subsetValues
		)));
	}

	public RealRange $range {
		get => $this->actualRange ??= new RealRange(
			$this->minValue(), $this->maxValue()
		);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'RealSubset',
			'values' => array_map(fn(Number $value): float => (float)(string)$value, $this->subsetValues)
		];
	}
}