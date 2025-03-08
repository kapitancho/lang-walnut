<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\ShapeType as ShapeTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

/** @psalm-immutable */
final class ShapeType implements ShapeTypeInterface, SupertypeChecker, JsonSerializable {

	private readonly Type $realValueType;

    public function __construct(
		private readonly MethodFinder $methodFinder,
		public readonly Type $declaredValueType,
    ) {}

	public Type $refType {
		get => $this->realValueType ??= $this->declaredValueType instanceof ProxyNamedType ?
			$this->declaredValueType->actualType : $this->declaredValueType;
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
	        $ofType instanceof ShapeTypeInterface => $this->refType->isSubtypeOf($ofType->refType),
	        $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false,
        };
    }

	private function isShapeOf(Type $ofType): bool {
		if ($ofType instanceof NamedType) {
			$method = $this->methodFinder->methodForType(
				$ofType,
				new MethodNameIdentifier(
					sprintf("as%s", $this->refType) // this is ugly
				)
			);
			if ($method instanceof CustomMethod) {
				if ($method->returnType->isSubtypeOf($this->refType)) {
					return true;
				}
			}
		}
		return false;
	}

	public function isSupertypeOf(Type $ofType): bool {
		return
			($ofType instanceof OpenType && $ofType->valueType->isSubtypeOf($this->refType)) ||
			$ofType->isSubtypeOf($this->refType) ||
			$this->isShapeOf($ofType);
	}

	public function __toString(): string {
		if ($this->refType instanceof AnyType) {
			return "Shape";
		}
		return sprintf(
			"Shape<%s>",
			$this->refType
		);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Shape', 'refType' => $this->refType];
	}
}