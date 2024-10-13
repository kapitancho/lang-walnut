<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\StringSubsetType as StringSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\StringType as StringTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Range\LengthRange;

final readonly class StringSubsetType implements StringSubsetTypeInterface, JsonSerializable {
	private LengthRange $range;

	/** @param list<StringValue> $subsetValues */
    public function __construct(
        private array $subsetValues
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof StringTypeInterface =>
                self::isInRange($this->subsetValues, $ofType->range()),
            $ofType instanceof StringSubsetTypeInterface =>
                self::isSubset($this->subsetValues, $ofType->subsetValues()),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	/** @param list<StringValue> $subsetValues */
    private static function isInRange(array $subsetValues, LengthRange $range): bool {
        foreach($subsetValues as $value) {
            if (!$range->lengthInRange(mb_strlen($value->literalValue()))) {
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

    /** @return list<StringValue> */
    public function subsetValues(): array {
        return $this->subsetValues;
    }

	public function contains(StringValue $value): bool {
		return in_array($value, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("String[%s]", implode(', ', $this->subsetValues));
	}

	private function minLength(): int {
		return min(array_map(
			static fn(StringValue $value): int =>
				mb_strlen($value->literalValue()), $this->subsetValues
		));
	}

	private function maxLength(): int {
		return max(array_map(
			static fn(StringValue $value): int =>
				mb_strlen($value->literalValue()), $this->subsetValues
		));
	}

	public function range(): LengthRange {
		return $this->range ??= new LengthRange(
			$this->minLength(), $this->maxLength()
		);
	}

	public function jsonSerialize(): array {
		return ['type' => 'StringSubset', 'values' => $this->subsetValues];
	}
}