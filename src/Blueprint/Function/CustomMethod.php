<?php

namespace Walnut\Lang\Blueprint\Function;

use Stringable;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

interface CustomMethod extends Method, Stringable {
	public Type $targetType { get; }
	public MethodNameIdentifier $methodName { get; }
	public Type $parameterType { get; }
	public Type $dependencyType { get; }
	public Type $returnType { get; }
	public FunctionBody $functionBody { get; }
}