<?php

namespace Walnut\Lang\Blueprint\Code\Execution;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface ExecutionResult extends ExecutionContext {
	public function typedValue(): TypedValue;
	public function value(): Value;
	public function valueType(): Type;

	public function withAddedVariableValue(
		VariableNameIdentifier $variableName,
		TypedValue $typedValue
	): self;

	public function withTypedValue(TypedValue $typedValue): self;
}