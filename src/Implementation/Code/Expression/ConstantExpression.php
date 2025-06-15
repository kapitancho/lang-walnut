<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\ConstantExpression as ConstantExpressionInterface;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Value\FunctionValue;

final readonly class ConstantExpression implements ConstantExpressionInterface, JsonSerializable {
	public function __construct(
		public Value $value
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		if ($this->value instanceof FunctionValue) {
			$this->value->selfAnalyse($analyserContext);
		}
		if ($this->value instanceof DataValue || $this->value instanceof MutableValue) {
			$this->value->selfAnalyse($analyserContext);
		}
		return $analyserContext->asAnalyserResult(
			$this->value->type,
			$analyserContext->programRegistry->typeRegistry->nothing
		);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$variableValueScope = $executionContext->variableValueScope;
		$value = $this->value;
		if ($value instanceof FunctionValue) {
			$value = $value->withVariableValueScope($variableValueScope);
		}
		return $executionContext->asExecutionResult(($value));
	}

	public function __toString(): string {
		return (string)$this->value;
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'constant',
			'value' => $this->value
		];
	}
}