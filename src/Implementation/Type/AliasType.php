<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\AliasType as AliasTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class AliasType implements AliasTypeInterface, SupertypeChecker, JsonSerializable {

    public function __construct(
	    private TypeNameIdentifier $typeName,
        private Type $aliasedType
    ) {}

	public function name(): TypeNameIdentifier {
		return $this->typeName;
    }

	public function aliasedType(): Type {
        return $this->aliasedType;
    }

    public function isSubtypeOf(Type $ofType): bool {
	    if ($ofType instanceof AliasTypeInterface && $this->typeName->equals($ofType->typeName)) {
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
		return (string)$this->typeName;
	}

	public function isSupertypeOf(Type $ofType): bool {
		return $ofType->isSubtypeOf($this->aliasedType);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Alias',
			'name' => $this->typeName,
			'aliasedType' => $this->aliasedType
		];
	}
}