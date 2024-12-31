<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\VariableNameExpression as VariableNameExpressionInterface;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;

final readonly class VariableNameExpression implements VariableNameExpressionInterface, JsonSerializable {
	public function __construct(
		private TypeRegistry $typeRegistry,
		public VariableNameIdentifier $variableName
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$type = $analyserContext->variableScope->typeOf($this->variableName);
		return $analyserContext->asAnalyserResult($type, $this->typeRegistry->nothing);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$variableValueScope = $executionContext->variableValueScope;
		$value = $variableValueScope->typedValueOf($this->variableName);
		return $executionContext->asExecutionResult($value);
	}

	public function __toString(): string {
		return (string)$this->variableName;
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'variableName',
			'variableName' => $this->variableName
		];
	}
}