<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\NumberRange as NumberRangeInterface;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType as IntegerSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\IntegerType as IntegerTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberRange;

final class IntegerSubsetType implements IntegerSubsetTypeInterface, JsonSerializable {

	private readonly IntegerTypeInterface $underlyingType;

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
			/** @phpstan-ignore-next-line instanceof.alwaysTrue */
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

	    $numberRange = new NumberRange(true,
		    ...array_map(
			    fn(Number $number): NumberInterval => NumberInterval::singleNumber($number),
			    $subsetValues
		    )
	    );
	    $this->underlyingType = new IntegerType(
		    $numberRange
	    );
    }

	public function isSubtypeOf(Type $ofType): bool {
		return $this->underlyingType->isSubtypeOf($ofType) ||
			($ofType instanceof SupertypeChecker && $ofType->isSupertypeOf($this));
	}

	public function contains(int|Number $value): bool {
		if (is_int($value)) {
			$value = new Number($value);
		}
		return in_array($value, $this->subsetValues);
	}
	
	public function __toString(): string {
		return sprintf("Integer[%s]", implode(', ', $this->subsetValues));
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'IntegerSubset',
			'values' => array_map(fn(Number $value): int => (int)(string)$value, $this->subsetValues)
		];
	}

	public NumberRangeInterface $numberRange {
		get {
			return $this->underlyingType->numberRange;
		}
	}
}