<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Function\CustomMethodDraft as CustomMethodDraftInterface;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft;
use Walnut\Lang\Blueprint\Function\MethodDraft;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodDraftRegistry;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodDraftRegistryBuilder as MethodDraftRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Function\CustomMethodDraft;

final class CustomMethodDraftRegistryBuilder implements MethodDraftRegistryBuilderInterface, CustomMethodDraftRegistry {

	/** @var array<string, list<CustomMethodDraftInterface>> $methods */
	public array $customMethods;

	public function __construct(
		private readonly TypeRegistry $typeRegistry
	) {
		$this->customMethods = [];
	}

	public function addMethodDraft(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBodyDraft $functionBody,
	): CustomMethodDraftInterface {
		$this->customMethods[$methodName->identifier] ??= [];
		$this->customMethods[$methodName->identifier][] = $method = new CustomMethodDraft(
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
	public function addConstructorMethodDraft(
		TypeNameIdentifier $typeName,
		Type $parameterType,
		Type $dependencyType,
		Type $errorType,
		FunctionBodyDraft $functionBody
	): CustomMethodDraftInterface {
		$type = $this->typeRegistry->typeByName($typeName);
		$returnType = match(true) {
			$type instanceof SealedType => $type->valueType,
			$type instanceof SubtypeType => $type->baseType,
			default => throw new CompilationException(
				"Constructors are only allowed for subtypes and sealed types",
			)
		};
		return $this->addMethodDraft(
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

	public function methodDraft(Type $targetType, MethodNameIdentifier $methodName): MethodDraft|UnknownMethod {
		foreach(array_reverse($this->customMethods[$methodName->identifier] ?? []) as $method) {
			if ($targetType->isSubtypeOf($method->targetType)) {
				return $method;
			}
		}
		return UnknownMethod::value;
	}
}