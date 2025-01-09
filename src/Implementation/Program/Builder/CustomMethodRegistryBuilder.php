<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Function\CustomMethod as CustomMethodInterface;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Function\CustomMethod;

final class CustomMethodRegistryBuilder implements CustomMethodRegistryBuilderInterface, MethodRegistry, CustomMethodRegistry {

	/** @var array<string, list<CustomMethodInterface>> $methods */
	public array $customMethods;

	public function __construct(
		private readonly TypeRegistry $typeRegistry
	) {
		$this->customMethods = [];
	}

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

	/** @throws CompilationException */
	public function addConstructorMethod(
		TypeNameIdentifier $typeName,
		Type $parameterType,
		Type $dependencyType,
		Type $errorType,
		FunctionBody $functionBody
	): CustomMethodInterface {
		$type = $this->typeRegistry->typeByName($typeName);
		$returnType = match(true) {
			$type instanceof SealedType => $type->valueType,
			$type instanceof SubtypeType => $type->baseType,
			default => throw new CompilationException(
				"Constructors are only allowed for subtypes and sealed types",
			)
		};
		return $this->addMethod(
			$this->typeRegistry->typeByName(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier($typeName),
			$parameterType,
			$dependencyType,
			$errorType instanceof NothingType ? $returnType : $this->typeRegistry->result(
				$returnType, $errorType
			),
			$functionBody
		);
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