<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ProxyNamedType as ProxyNamedTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final class ProxyNamedType implements ProxyNamedTypeInterface, SupertypeChecker, JsonSerializable {

    public function __construct(
	    public readonly TypeNameIdentifier $name,
        private readonly TypeRegistry $typeRegistry
    ) {}

	// @codeCoverageIgnoreStart
	public Type $actualType {
		get => $this->typeRegistry->withName($this->name);
	}

	public function isSubtypeOf(Type $ofType): bool {
		if ($ofType instanceof ProxyNamedTypeInterface && $this->name->equals($ofType->name)) {
			return true;
		}
        return $this->actualType->isSubtypeOf($ofType);
    }

	public function __toString(): string {
		return (string)$this->actualType;
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Proxy',
			'proxy' => (string)$this->actualType
		];
	}


	public function isSupertypeOf(Type $ofType): bool {
		//$t = $this->getActualType();
		//TODO - fix the endless recursion
		if (count(debug_backtrace()) > 150) {
			return false;
		}
		//if (!($t instanceof SupertypeChecker)) {
			return $ofType->isSubtypeOf($this->actualType);
		//}
		//return false;
	}
	// @codeCoverageIgnoreEnd
}