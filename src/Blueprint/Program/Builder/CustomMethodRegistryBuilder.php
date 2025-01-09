<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Type\Type;

interface CustomMethodRegistryBuilder {
	public function addMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBody $functionBody,
	): CustomMethod;

	/** @throws CompilationException */
	public function addConstructorMethod(
		TypeNameIdentifier $typeName,
		Type $parameterType,
		Type $dependencyType,
		Type $errorType,
		FunctionBody $functionBody
	): CustomMethod;
}