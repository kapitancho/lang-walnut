<?php

namespace Walnut\Lang\Implementation\Code\Execution;

use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult as ExecutionResultInterface;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final class ExecutionResult implements ExecutionResultInterface {

	public function __construct(
		public readonly ProgramRegistry $programRegistry,
		public readonly VariableValueScope $variableValueScope,
		public readonly TypedValue         $typedValue
	) {}

	public function withAddedVariableValue(VariableNameIdentifier $variableName, TypedValue $typedValue): self {
		return new self(
			$this->programRegistry,
			$this->variableValueScope->withAddedVariableValue($variableName, $typedValue),
			$this->typedValue
		);
	}

	public function asExecutionResult(TypedValue $typedValue): ExecutionResult {
		return new self(
			$this->programRegistry,
			$this->variableValueScope,
			$typedValue
		);
	}

	public Value $value { get => $this->typedValue->value; }
	public Type $valueType { get => $this->typedValue->type; }

	public function withTypedValue(TypedValue $typedValue): ExecutionResultInterface {
		return $this->asExecutionResult($typedValue);
	}
}