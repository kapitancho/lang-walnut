<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\IdentifierException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ShapeType as ShapeTypeInterface;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

/** @psalm-immutable */
final class ShapeType implements ShapeTypeInterface, JsonSerializable {
	use BaseType;

	private readonly Type $realValueType;

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
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
		$baseType = $this->toBaseType($this->refType);
		$refTypes = $baseType instanceof UnionType ? $baseType->types : [];
		foreach([$this->refType, ...$refTypes] as $checkType) {
			try {
				$methodName = new MethodNameIdentifier(
					sprintf("as%s", $checkType) // this is ugly
				);
				$method = $this->methodFinder->methodForType(
					$ofType,
					$methodName
				);
				if ($method instanceof CustomMethod) {
					if ($method->returnType->isSubtypeOf($this->refType)) {
						return true;
					}
				} elseif ($method instanceof NativeMethod) {
					$detectedType = $method->analyse(
						$this->typeRegistry,
						$this->methodFinder,
						$ofType,
						$this->typeRegistry->null,
					);
					return $detectedType->isSubtypeOf($this->refType);
				}
			} catch (IdentifierException) {}
		}
		return false;
	}

	public function isSupertypeOf(Type $ofType): bool {
		return
			($ofType instanceof DataType && $ofType->valueType->isSubtypeOf($this)) ||
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