<?php

namespace Walnut\Lang\Implementation\Code\Execution;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext as AnalyserContextInterface;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult as AnalyserResultInterface;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext as ExecutionContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserResult;

final readonly class ExecutionContext implements ExecutionContextInterface {

	public VariableScope $variableScope;
	public TypeRegistry $typeRegistry;
	public ValueRegistry $valueRegistry;
	public MethodFinder $methodFinder;

	public function __construct(
		public ProgramRegistry $programRegistry,
		public VariableValueScope $variableValueScope
	) {
		$this->variableScope = $this->variableValueScope;
		$this->valueRegistry = $this->programRegistry->valueRegistry;
		$this->typeRegistry = $this->programRegistry->typeRegistry;
		$this->methodFinder = $this->programRegistry->methodFinder;
	}

	public function withAddedVariableValue(VariableNameIdentifier $variableName, Value $value): self {
		return new self(
			$this->programRegistry,
			$this->variableValueScope->withAddedVariableValue($variableName, $value)
		);
	}

	public function asExecutionResult(Value $typedValue): ExecutionResult {
		return new ExecutionResult(
			$this->programRegistry,
			$this->valueRegistry,
			$this->variableValueScope,
			$typedValue
		);
	}

	// @codeCoverageIgnoreStart
	public function withAddedVariableType(VariableNameIdentifier $variableName, Type $variableType): AnalyserContextInterface {
		return new AnalyserContext(
			$this->programRegistry->typeRegistry,
			$this->programRegistry->methodFinder,
			$this->variableScope->withAddedVariableType($variableName, $variableType),
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