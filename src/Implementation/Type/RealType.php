<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Range\RealRange;
use Walnut\Lang\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class RealType implements RealTypeInterface, JsonSerializable {

    public function __construct(private RealRange $range) {}

    public function range(): RealRange {
        return $this->range;
    }

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof RealTypeInterface => $this->range->isSubRangeOf($ofType->range()),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function __toString(): string {
		$range = (string)$this->range;
		return sprintf("Real%s", $range === '..' ? '' : "<$range>");
	}

	public function jsonSerialize(): array {
		return ['type' => 'Real', 'range' => $this->range];
	}
}