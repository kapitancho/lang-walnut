<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\MutableType as MutableTypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType as TypeTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

/** @psalm-immutable */
final readonly class TypeType implements TypeTypeInterface, JsonSerializable {

    public function __construct(
        private Type $refType
    ) {}

    public function refType(): Type {
        return $this->refType;
    }

    public function isSubtypeOf(Type $ofType): bool {
		if ($ofType instanceof TypeTypeInterface) {
			$ofTypeRef = $ofType->refType();
			if ($this->refType instanceof MutableTypeInterface && $ofTypeRef instanceof MutableTypeInterface) {
				return $this->refType->valueType()->isSubtypeOf($ofTypeRef->valueType());
			}
		}
        return match(true) {
	        $ofType instanceof TypeTypeInterface => $this->refType->isSubtypeOf($ofType->refType()),
	        $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false,
        };
    }

	public function __toString(): string {
		return sprintf(
			"Type<%s>",
			$this->refType
		);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Type', 'refType' => $this->refType];
	}
}