<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\NumberRange as NumberRangeInterface;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\RealSubsetType as RealSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberRange;

final class RealSubsetType implements RealSubsetTypeInterface, JsonSerializable {

	private readonly RealType $underlyingType;

    /** @param list<Number> $subsetValues */
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
		    if (!$value instanceof Number) {
			    // @codeCoverageIgnoreStart
			    throw new InvalidArgumentException(
				    sprintf("Invalid value: '%s'", $value)
			    );
			    // @codeCoverageIgnoreEnd
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

		$numberRange = new NumberRange(false,
			...array_map(
				fn(Number $number): NumberInterval => NumberInterval::singleNumber($number),
				$subsetValues
			)
		);
		$this->underlyingType = new RealType(
			$numberRange
		);
    }

    public function isSubtypeOf(Type $ofType): bool {
		return $this->underlyingType->isSubtypeOf($ofType) ||
			($ofType instanceof SupertypeChecker && $ofType->isSupertypeOf($this));
    }

	public function contains(IntegerValue|RealValue $value): bool {
		return in_array($value->literalValue, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("Real[%s]", implode(', ', $this->subsetValues));
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'RealSubset',
			'values' => array_map(fn(Number $value): float => (float)(string)$value, $this->subsetValues)
		];
	}

	public NumberRangeInterface $numberRange {
		get {
			return $this->underlyingType->numberRange;
		}
	}
}