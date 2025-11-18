<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\MutableType as MutableTypeInterface;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType as TypeTypeInterface;

/** @psalm-immutable */
final readonly class TypeType implements TypeTypeInterface, JsonSerializable {

    public function __construct(
        public Type $refType
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
		if ($ofType instanceof TypeTypeInterface) {
			$ofTypeRef = $ofType->refType;
			if ($this->refType instanceof MutableTypeInterface && $ofTypeRef instanceof MutableTypeInterface) {
				return $this->refType->valueType->isSubtypeOf($ofTypeRef->valueType);
			}
		}
        return match(true) {
	        $ofType instanceof TypeTypeInterface => $this->refType->isSubtypeOf($ofType->refType),
	        $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false,
        };
    }

	public function __toString(): string {
		if ($this->refType instanceof AnyType) {
			return "Type";
		}
		return sprintf(
			"Type<%s>",
			$this->refType
		);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Type', 'refType' => $this->refType];
	}
}