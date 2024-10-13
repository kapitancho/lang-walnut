<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ProxyNamedType as ProxyNamedTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class ProxyNamedType implements ProxyNamedTypeInterface, SupertypeChecker, JsonSerializable {

    public function __construct(
	    private TypeNameIdentifier $typeName,
        private TypeRegistry $typeRegistry
    ) {}

	public function name(): TypeNameIdentifier {
		return $this->typeName;
    }

	// @codeCoverageIgnoreStart
	public function getActualType(): Type {
		return $this->typeRegistry->withName($this->typeName);
	}

	public function isSubtypeOf(Type $ofType): bool {
		if ($ofType instanceof ProxyNamedTypeInterface && $this->typeName->equals($ofType->name())) {
			return true;
		}
        return $this->getActualType()->isSubtypeOf($ofType);
    }

	public function __toString(): string {
		return (string)$this->getActualType();
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Proxy',
			'proxy' => (string)$this->getActualType()
		];
	}


	public function isSupertypeOf(Type $ofType): bool {
		//$t = $this->getActualType();
		//TODO - fix the endless recursion
		if (count(debug_backtrace()) > 150) {
			return false;
		}
		//if (!($t instanceof SupertypeChecker)) {
			return $ofType->isSubtypeOf($this->getActualType());
		//}
		//return false;
	}
	// @codeCoverageIgnoreEnd
}