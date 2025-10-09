<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Type\Type;

interface CustomMethodRegistryBuilder {
	public function addMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		VariableNameIdentifier|null $parameterName,
		Type $dependencyType,
		VariableNameIdentifier|null $dependencyName,
		Type $returnType,
		FunctionBody $functionBody
	): CustomMethod;
}