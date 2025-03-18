<?php

namespace Walnut\Lang\Implementation\Code\Execution;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext as AnalyserContextInterface;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult as AnalyserResultInterface;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext as ExecutionContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserResult;

final readonly class ExecutionContext implements ExecutionContextInterface {

	public VariableScope $variableScope;

	public function __construct(
		public ProgramRegistry $programRegistry,
		public VariableValueScope $variableValueScope
	) {
		$this->variableScope = $this->variableValueScope;
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
			$this->variableValueScope,
			$typedValue
		);
	}

	public function withAddedVariableType(VariableNameIdentifier $variableName, Type $variableType): AnalyserContextInterface {
		return new AnalyserContext(
			$this->programRegistry,
			$this->variableScope->withAddedVariableType($variableName, $variableType),
		);
	}

	public function asAnalyserResult(Type $expressionType, Type $returnType): AnalyserResultInterface {
		return new AnalyserResult(
			$this->programRegistry,
			$this->variableScope,
			$expressionType,
			$returnType
		);
	}
}