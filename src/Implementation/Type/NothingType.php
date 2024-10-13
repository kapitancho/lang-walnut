<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\NothingType as NothingTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class NothingType implements NothingTypeInterface, JsonSerializable {
    public function isSubtypeOf(Type $ofType): bool {
        return true;
    }

	public function __toString(): string {
		return 'Nothing';
	}

	public function jsonSerialize(): array {
		return ['type' => 'Nothing'];
	}
}