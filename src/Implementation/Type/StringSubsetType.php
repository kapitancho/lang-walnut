<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Blueprint\Type\StringSubsetType as StringSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\StringType as StringTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Common\Range\LengthRange;

final class StringSubsetType implements StringSubsetTypeInterface, JsonSerializable {

	private readonly LengthRange $actualRange;

	/** @param list<StringValue> $subsetValues */
    public function __construct(
        public readonly array $subsetValues
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof StringTypeInterface =>
                self::isInRange($this->subsetValues, $ofType->range),
            $ofType instanceof StringSubsetTypeInterface =>
                self::isSubset($this->subsetValues, $ofType->subsetValues),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	/** @param list<StringValue> $subsetValues */
    private static function isInRange(array $subsetValues, LengthRange $range): bool {
	    return array_all($subsetValues, fn($value) => $range->lengthInRange(
			new Number(mb_strlen($value->literalValue))
	    ));
    }

    private static function isSubset(array $subset, array $superset): bool {
	    return array_all($subset, fn($value) => in_array($value, $superset));
    }

	public function contains(StringValue $value): bool {
		return in_array($value, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("String[%s]", implode(', ', $this->subsetValues));
	}

	private function minLength(): Number {
		return new Number(min(array_map(
			static fn(StringValue $value): int =>
				mb_strlen($value->literalValue), $this->subsetValues
		)));
	}

	private function maxLength(): Number {
		return new Number(max(array_map(
			static fn(StringValue $value): int =>
				mb_strlen($value->literalValue), $this->subsetValues
		)));
	}

	public LengthRange $range {
		get => $this->actualRange ??= new LengthRange(
			$this->minLength(), $this->maxLength()
		);
	}

	public function jsonSerialize(): array {
		return ['type' => 'StringSubset', 'values' => $this->subsetValues];
	}
}