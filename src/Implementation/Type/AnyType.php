<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\AnyType as AnyTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class AnyType implements AnyTypeInterface, SupertypeChecker, JsonSerializable {

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
			$ofType instanceof AnyTypeInterface => true,
	        $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
	        default => false
		};
    }

    public function isSupertypeOf(Type $ofType): bool {
        return true;
    }

	public function __toString(): string {
		return 'Any';
	}

	public function jsonSerialize(): array {
		return ['type' => 'Any'];
	}
}