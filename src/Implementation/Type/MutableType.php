<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\MutableType as MutableTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final class MutableType implements MutableTypeInterface, JsonSerializable {

	private readonly Type $realValueType;

    public function __construct(
        public readonly Type $declaredValueType
    ) {}

	public Type $valueType {
		get => $this->realValueType ??= $this->declaredValueType instanceof ProxyNamedType ?
			$this->declaredValueType->actualType : $this->declaredValueType;
	}

    public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof MutableTypeInterface =>
				$this->valueType->isSubtypeOf($ofType->valueType) &&
	            $ofType->valueType->isSubtypeOf($this->valueType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

	public function __toString(): string {
		return sprintf("Mutable<%s>", $this->valueType);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Mutable', 'valueType' => $this->valueType];
	}
}