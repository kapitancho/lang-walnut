<?php

namespace Walnut\Lang\Implementation\Code\Execution;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext as AnalyserContextInterface;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult as AnalyserResultInterface;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext as ExecutionContextInterface;
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
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserResult;

final readonly class ExecutionContext implements ExecutionContextInterface {

	public VariableScope $variableScope;
	public MethodAnalyser $methodAnalyser;

	public function __construct(
		public DependencyContainer $dependencyContainer,
		public ValueRegistry $valueRegistry,
		public TypeRegistry $typeRegistry,
		public MethodContext $methodContext,
		public VariableValueScope $variableValueScope
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
			$this->variableValueScope->withAddedVariableValue($variableName, $value)
		);
	}

	public function asExecutionResult(Value $typedValue): ExecutionResult {
		return new ExecutionResult(
			$this->dependencyContainer,
			$this->valueRegistry,
			$this->typeRegistry,
			$this->methodContext,
			$this->variableValueScope,
			$typedValue
		);
	}

	// @codeCoverageIgnoreStart
	public function withAddedVariableType(VariableNameIdentifier $variableName, Type $variableType): AnalyserContextInterface {
		return new AnalyserContext(
			$this->typeRegistry,
			$this->methodContext,
			$this->variableScope->withAddedVariableType($variableName, $variableType),
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