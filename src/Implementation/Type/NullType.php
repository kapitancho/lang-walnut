<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\NullType as NullTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\NullValue;

final readonly class NullType implements NullTypeInterface, JsonSerializable {

    public function __construct(
        public TypeNameIdentifier $name,
        public NullValue           $value
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true){
			$ofType instanceof NullTypeInterface => true,
	        $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
	        default => false
	    };
    }

	public function __toString(): string {
		return 'Null';
	}

	public function jsonSerialize(): array {
		return ['type' => 'Null'];
	}
}