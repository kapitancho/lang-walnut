<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Range\LengthRange;
use Walnut\Lang\Blueprint\Type\StringType as StringTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class StringType implements StringTypeInterface, JsonSerializable {

    public function __construct(private LengthRange $range) {}

    public function range(): LengthRange {
        return $this->range;
    }

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof StringTypeInterface => $this->range->isSubRangeOf($ofType->range()),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function __toString(): string {
		$range = (string)$this->range;
		return sprintf("String%s", $range === '..' ? '' : "<$range>");
	}

	public function jsonSerialize(): array {
		return ['type' => 'String', 'range' => $this->range];
	}
}