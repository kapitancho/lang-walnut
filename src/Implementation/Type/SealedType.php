<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\SealedType as SealedTypeInterface;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class SealedType implements SealedTypeInterface, JsonSerializable {

    public function __construct(
	    public TypeNameIdentifier $name,
        public Type         $valueType
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof SealedTypeInterface => $this->name->equals($ofType->name),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

	public function __toString(): string {
		return (string)$this->name;
	}

	public function jsonSerialize(): array {
		return ['type' => 'Sealed', 'name' => $this->name, 'valueType' => $this->valueType];
	}
}