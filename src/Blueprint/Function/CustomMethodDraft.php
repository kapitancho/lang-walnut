<?php

namespace Walnut\Lang\Blueprint\Function;

use Stringable;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

interface CustomMethodDraft extends Stringable {
	public Type $targetType { get; }
	public MethodNameIdentifier $methodName { get; }
	public Type $parameterType { get; }
	public Type $dependencyType { get; }
	public Type $returnType { get; }
	public FunctionBodyDraft $functionBody { get; }
}