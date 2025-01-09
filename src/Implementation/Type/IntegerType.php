<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\IntegerRange;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType as IntegerTypeInterface;
use Walnut\Lang\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class IntegerType implements IntegerTypeInterface, JsonSerializable {

    public function __construct(
		private TypeRegistry $typeRegistry,
		public IntegerRange $range
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof IntegerSubsetType => $this->isSubtypeOfSubset($ofType),
            $ofType instanceof IntegerTypeInterface => $this->range->isSubRangeOf($ofType->range),
            $ofType instanceof RealTypeInterface => $this->asRealType()->isSubTypeOf($ofType),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

    private function asRealType(): RealTypeInterface {
	    $range = $this->range->asRealRange();
        return $this->typeRegistry->real($range->minValue, $range->maxValue);
    }

	public function __toString(): string {
		$range = (string)$this->range;
		return sprintf("Integer%s", $range === '..' ? '' : "<$range>");
	}

	private function isSubtypeOfSubset(IntegerSubsetType $ofType): bool {
		$min = $this->range->minValue;
		$max = $this->range->maxValue;
		return $min !== MinusInfinity::value && $max !== PlusInfinity::value &&
			$ofType->range->minValue <= $min && $ofType->range->maxValue >= $max &&
			1 + (int)(string)$this->range->maxValue - (int)(string)$this->range->minValue === count(
				array_filter($ofType->subsetValues, static fn(Number $value): bool =>
					$value >= $min && $value <= $max
			));
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Integer',
			'range' => $this->range
		];
	}
}