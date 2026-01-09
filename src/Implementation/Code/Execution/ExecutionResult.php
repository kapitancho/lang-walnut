<?php

namespace Walnut\Lang\Implementation\Code\Execution;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult as AnalyserResultInterface;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult as ExecutionResultInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\MethodContext;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserResult;

final class ExecutionResult implements ExecutionResultInterface {

	public readonly VariableScope $variableScope;
	public readonly MethodAnalyser $methodAnalyser;

	public function __construct(
		public readonly DependencyContainer $dependencyContainer,
		public readonly ValueRegistry $valueRegistry,
		public readonly TypeRegistry $typeRegistry,
		public readonly MethodContext $methodContext,
		public readonly VariableValueScope $variableValueScope,
		public readonly Value $typedValue
	) {
		$this->variableScope = $variableValueScope;
		$this->methodAnalyser = $methodContext;
	}

	public function withAddedVariableValue(VariableNameIdentifier $variableName, Value $value): self {
		return new self(
			$this->dependencyContainer,
			$this->valueRegistry,
			$this->typeRegistry,
			$this->methodContext,
			$this->variableValueScope->withAddedVariableValue($variableName, $value),
			$this->typedValue
		);
	}

	public function asExecutionResult(Value $typedValue): ExecutionResult {
		return new self(
			$this->dependencyContainer,
			$this->valueRegistry,
			$this->typeRegistry,
			$this->methodContext,
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
			$this->methodContext,
			$this->variableScope->withAddedVariableType($variableName, $variableType),
			$this->typedValue->type,
			$this->typedValue->type,
		);
	}

	public function asAnalyserResult(Type $expressionType, Type $returnType): AnalyserResultInterface {
		return new AnalyserResult(
			$this->typeRegistry,
			$this->methodContext,
			$this->variableScope,
			$expressionType,
			$returnType
		);
	}
	// @codeCoverageIgnoreEnd
}