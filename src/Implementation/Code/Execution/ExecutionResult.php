<?php

namespace Walnut\Lang\Implementation\Code\Execution;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult as AnalyserResultInterface;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult as ExecutionResultInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserResult;

final class ExecutionResult implements ExecutionResultInterface {

	public readonly VariableScope $variableScope;
	public TypeRegistry $typeRegistry;
	public MethodFinder $methodFinder;

	public function __construct(
		public readonly ProgramRegistry $programRegistry,
		public readonly ValueRegistry $valueRegistry,
		public readonly VariableValueScope $variableValueScope,
		public readonly Value $typedValue
	) {
		$this->variableScope = $variableValueScope;
		$this->typeRegistry = $this->programRegistry->typeRegistry;
		$this->methodFinder = $this->programRegistry->methodFinder;
	}

	public function withAddedVariableValue(VariableNameIdentifier $variableName, Value $value): self {
		return new self(
			$this->programRegistry,
			$this->valueRegistry,
			$this->variableValueScope->withAddedVariableValue($variableName, $value),
			$this->typedValue
		);
	}

	public function asExecutionResult(Value $typedValue): ExecutionResult {
		return new self(
			$this->programRegistry,
			$this->valueRegistry,
			$this->variableValueScope,
			$typedValue
		);
	}

	public Value $value { get => $this->typedValue; }
	public Type $valueType { get => $this->typedValue->type; }

	public function withValue(Value $typedValue): ExecutionResultInterface {
		return $this->asExecutionResult($typedValue);
	}

	// @codeCoverageIgnoreStart
	public function withAddedVariableType(VariableNameIdentifier $variableName, Type $variableType): AnalyserResultInterface {
		return new AnalyserResult(
			$this->typeRegistry,
			$this->methodFinder,
			$this->variableScope->withAddedVariableType($variableName, $variableType),
			$this->typedValue->type,
			$this->typedValue->type,
		);
	}

	public function asAnalyserResult(Type $expressionType, Type $returnType): AnalyserResultInterface {
		return new AnalyserResult(
			$this->typeRegistry,
			$this->methodFinder,
			$this->variableScope,
			$expressionType,
			$returnType
		);
	}
	// @codeCoverageIgnoreEnd
}