<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Range\LengthRange;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\BytesType as BytesTypeInterface;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class BytesType implements BytesTypeInterface, JsonSerializable {

    public function __construct(public LengthRange $range) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof BytesTypeInterface => $this->range->isSubRangeOf($ofType->range),
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
		return sprintf("Bytes%s", $range === '..' ? '' : "<$range>");
	}

	public function jsonSerialize(): array {
		return ['type' => 'Bytes', 'range' => $this->range];
	}
}