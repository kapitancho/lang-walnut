<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\LengthRange;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\ByteArrayType as ByteArrayTypeInterface;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class ByteArrayType implements ByteArrayTypeInterface, JsonSerializable {

    public function __construct(public LengthRange $range) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof ByteArrayTypeInterface => $this->range->isSubRangeOf($ofType->range),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function __toString(): string {
		$range = (string)$this->range;
		if (
			$this->range->maxLength !== PlusInfinity::value &&
			(string)$this->range->minLength === (string)$this->range->maxLength
		) {
			$range = (string)$this->range->minLength;
		}
		return sprintf("ByteArray%s", $range === '..' ? '' : "<$range>");
	}

	public function jsonSerialize(): array {
		return ['type' => 'ByteArray', 'range' => $this->range];
	}
}