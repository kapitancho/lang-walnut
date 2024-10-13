<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\MutableType as MutableTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class MutableType implements MutableTypeInterface, JsonSerializable {

	private Type $realValueType;

    public function __construct(
        private Type $valueType
    ) {}

    public function valueType(): Type {
        return $this->realValueType ??= $this->valueType instanceof ProxyNamedType ?
            $this->valueType->getActualType() : $this->valueType;
    }

    public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof MutableTypeInterface =>
				$this->valueType()->isSubtypeOf($ofType->valueType()) &&
	            $ofType->valueType()->isSubtypeOf($this->valueType()),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

	public function __toString(): string {
		return sprintf("Mutable<%s>", $this->valueType());
	}

	public function jsonSerialize(): array {
		return ['type' => 'Mutable', 'valueType' => $this->valueType];
	}
}