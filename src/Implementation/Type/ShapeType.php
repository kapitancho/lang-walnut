<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\MutableType as MutableTypeInterface;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\ShapeType as ShapeTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

/** @psalm-immutable */
final readonly class ShapeType implements ShapeTypeInterface, SupertypeChecker, JsonSerializable {

    public function __construct(
        public Type $refType
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
		if ($ofType instanceof ShapeTypeInterface) {
			$ofTypeRef = $ofType->refType;
			if ($this->refType instanceof MutableTypeInterface && $ofTypeRef instanceof MutableTypeInterface) {
				return $this->refType->valueType->isSubtypeOf($ofTypeRef->valueType);
			}
		}
        return match(true) {
	        $ofType instanceof ShapeTypeInterface => $this->refType->isSubtypeOf($ofType->refType),
	        $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false,
        };
    }

	public function isSupertypeOf(Type $ofType): bool {
		return
			($ofType instanceof OpenType && $ofType->valueType->isSubtypeOf($this->refType)) ||
			$ofType->isSubtypeOf($this->refType);
	}

	public function __toString(): string {
		if ($this->refType instanceof AnyType) {
			return "Shape";
		}
		return sprintf(
			"Shape<%s>",
			$this->refType
		);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Shape', 'refType' => $this->refType];
	}
}