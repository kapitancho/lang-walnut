<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\AliasType as AliasTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class AliasType implements AliasTypeInterface, SupertypeChecker, JsonSerializable {

    public function __construct(
	    public TypeNameIdentifier $name,
        public Type               $aliasedType
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
	    if ($ofType instanceof AliasTypeInterface && $this->name->equals($ofType->name)) {
            return true;
        }
	    return (
			$this->aliasedType->isSubtypeOf($ofType)
		) || (
			$ofType instanceof SupertypeChecker &&
			$ofType->isSupertypeOf($this)
		);
    }

	public function __toString(): string {
		return (string)$this->name;
	}

	public function isSupertypeOf(Type $ofType): bool {
		return $ofType->isSubtypeOf($this->aliasedType);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Alias',
			'name' => $this->name,
			'aliasedType' => $this->aliasedType
		];
	}
}