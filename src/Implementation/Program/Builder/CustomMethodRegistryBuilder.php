<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod as CustomMethodInterface;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Function\CustomMethod;

final class CustomMethodRegistryBuilder implements CustomMethodRegistryBuilderInterface, MethodRegistry, CustomMethodRegistry {

	/** @var array<string, list<CustomMethodInterface>> $methods */
	public array $customMethods = [];

	public function addMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBody $functionBody,
	): CustomMethodInterface {
		$this->customMethods[$methodName->identifier] ??= [];
		$this->customMethods[$methodName->identifier][] = $method = new CustomMethod(
			$targetType,
			$methodName,
			$parameterType,
			$dependencyType,
			$returnType,
			$functionBody,
		);
		return $method;
	}

	public function method(Type $targetType, MethodNameIdentifier $methodName): Method|UnknownMethod {
		foreach(array_reverse($this->customMethods[$methodName->identifier] ?? []) as $method) {
			if ($targetType->isSubtypeOf($method->targetType)) {
				return $method;
			}
		}
		return UnknownMethod::value;
	}
}