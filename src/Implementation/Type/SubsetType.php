<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\SubsetType as SubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class SubsetType implements SubsetTypeInterface, JsonSerializable {

    public function __construct(
	    public TypeNameIdentifier $name,
        public Type               $valueType
    ) {}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof SubsetTypeInterface =>
				$this->name->equals($ofType->name) ||
				$this->valueType->isSubtypeOf($ofType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this) ||
				$this->valueType->isSubtypeOf($ofType),
			default => $this->valueType->isSubtypeOf($ofType)
		};
    }

	public function __toString(): string {
		return (string)$this->name;
	}

	public function jsonSerialize(): array {
		return ['type' => 'Subset', 'name' => $this->name, 'valueType' => $this->valueType];
	}

}