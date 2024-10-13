<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\OptionalKeyType as OptionalKeyTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class OptionalKeyType implements OptionalKeyTypeInterface, SupertypeChecker, JsonSerializable {

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
			$ofType instanceof OptionalKeyTypeInterface => $this->valueType()->isSubtypeOf($ofType->valueType()),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function isSupertypeOf(Type $ofType): bool {
		return $ofType->isSubtypeOf($this->valueType());
	}

	public function __toString(): string {
		return sprintf("OptionalKey<%s>", $this->valueType());
	}

	public function jsonSerialize(): array {
		return ['type' => 'OptionalKey', 'valueType' => $this->valueType];
	}
}