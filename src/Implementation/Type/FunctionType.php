<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\FunctionType as FunctionTypeInterface;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;

final class FunctionType implements FunctionTypeInterface, JsonSerializable {

	private readonly Type $realParameterType;
	private readonly Type $realReturnType;

    public function __construct(
        private readonly Type $declaredParameterType,
        private readonly Type $declaredReturnType
    ) {}

	public Type $parameterType {
		get {
			return $this->realParameterType ??= $this->declaredParameterType instanceof ProxyNamedType ?
				$this->declaredParameterType->actualType : $this->declaredParameterType;
		}
	}

	public Type $returnType {
		get {
			return $this->realReturnType ??= $this->declaredReturnType instanceof ProxyNamedType ?
				$this->declaredReturnType->actualType : $this->declaredReturnType;
		}
	}

    public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof FunctionTypeInterface =>
				$ofType->parameterType->isSubtypeOf($this->parameterType) &&
				$this->returnType->isSubtypeOf($ofType->returnType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function __toString(): string {
		return sprintf(
			'^%s => %s',
			$this->parameterType,
			$this->returnType,
		);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Function',
			'parameter' => $this->parameterType,
			'return' => $this->returnType
		];
	}
}