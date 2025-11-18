<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\OptionalKeyType as OptionalKeyTypeInterface;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;

final class OptionalKeyType implements OptionalKeyTypeInterface, SupertypeChecker, JsonSerializable {

	private readonly Type $realValueType;

    public function __construct(
        private readonly Type $declaredValueType
    ) {}

	public Type $valueType {
		get => $this->realValueType ??= $this->declaredValueType instanceof ProxyNamedType ?
            $this->declaredValueType->actualType : $this->declaredValueType;
    }

    public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof OptionalKeyTypeInterface => $this->valueType->isSubtypeOf($ofType->valueType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function isSupertypeOf(Type $ofType): bool {
		return $ofType->isSubtypeOf($this->valueType);
	}

	public function __toString(): string {
		return sprintf("OptionalKey<%s>", $this->valueType);
	}

	public function jsonSerialize(): array {
		return ['type' => 'OptionalKey', 'valueType' => $this->valueType];
	}
}