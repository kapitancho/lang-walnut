<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\NumberRange as NumberRangeInterface;
use Walnut\Lang\Blueprint\Type\IntegerType as IntegerTypeInterface;
use Walnut\Lang\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;

final readonly class IntegerType implements IntegerTypeInterface, JsonSerializable {
    public function __construct(
		public NumberRangeInterface $numberRange,
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof IntegerTypeInterface => $ofType->numberRange->containsRange($this->numberRange),
	        $ofType instanceof RealTypeInterface => $ofType->numberRange->containsRange($this->numberRange),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function contains(IntegerValue $value): bool {
		return $this->numberRange->contains($value->literalValue);
	}

	public function __toString(): string {
		$range = (string)$this->numberRange;
		if (count($this->numberRange->intervals) === 1) {
			$range = preg_replace('#((^\(-Infinity|^\[)(.*?)(\+Infinity\)$|]$))#', '$3', $range);
		}
		$range = preg_replace('#[+-]Infinity#', '', $range);
		return sprintf("Integer%s", $range === '..' ? '' : "<$range>");
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Integer',
			'range' => $this->numberRange
		];
	}
}