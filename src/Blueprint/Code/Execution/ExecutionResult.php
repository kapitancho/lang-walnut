<?php

namespace Walnut\Lang\Blueprint\Code\Execution;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface ExecutionResult extends ExecutionContext {
	public TypedValue $typedValue { get; }
	public Value $value { get; }
	public Type $valueType { get; }

	public function withAddedVariableValue(
		VariableNameIdentifier $variableName,
		TypedValue $typedValue
	): self;

	public function withTypedValue(TypedValue $typedValue): self;
}