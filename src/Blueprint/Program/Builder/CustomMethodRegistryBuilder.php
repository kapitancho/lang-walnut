<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethodDraft;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft;
use Walnut\Lang\Blueprint\Type\Type;

interface CustomMethodRegistryBuilder {
	public function addMethodDraft(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBodyDraft $functionBody,
	): CustomMethodDraft;
}