<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\SubtypeType as SubtypeTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class SubtypeType implements SubtypeTypeInterface, JsonSerializable {

    public function __construct(
	    private TypeNameIdentifier $typeName,
        private Type $baseType
    ) {}

	public function name(): TypeNameIdentifier {
		return $this->typeName;
    }

	public function baseType(): Type {
        return $this->baseType;
    }

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof SubtypeTypeInterface => $this->typeName->equals($ofType->name()),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this) ||
				$this->baseType->isSubtypeOf($ofType),
			default => $this->baseType->isSubtypeOf($ofType)
		};
    }

	public function __toString(): string {
		return (string)$this->typeName;
	}

	public function jsonSerialize(): array {
		return ['type' => 'Subtype', 'name' => $this->typeName, 'baseType' => $this->baseType];
	}

}