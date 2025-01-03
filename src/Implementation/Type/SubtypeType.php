<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\SubtypeType as SubtypeTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class SubtypeType implements SubtypeTypeInterface, JsonSerializable {

    public function __construct(
	    public TypeNameIdentifier $name,
        public Type               $baseType
    ) {}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof SubtypeTypeInterface => $this->name->equals($ofType->name),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this) ||
				$this->baseType->isSubtypeOf($ofType),
			default => $this->baseType->isSubtypeOf($ofType)
		};
    }

	public function __toString(): string {
		return (string)$this->name;
	}

	public function jsonSerialize(): array {
		return ['type' => 'Subtype', 'name' => $this->name, 'baseType' => $this->baseType];
	}

}