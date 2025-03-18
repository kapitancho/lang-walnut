<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\ConstantExpression;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\VariableAssignmentExpression as VariableAssignmentExpressionInterface;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Value\FunctionValue;

final readonly class VariableAssignmentExpression implements VariableAssignmentExpressionInterface, JsonSerializable {
	public function __construct(
		public VariableNameIdentifier $variableName,
		public Expression $assignedExpression
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$innerFn = null;
		if ($this->assignedExpression instanceof ConstantExpression &&
			($v = $this->assignedExpression->value) instanceof FunctionValue
		) {
			$innerFn = $this->variableName;
			$analyserContext = $analyserContext->withAddedVariableType(
				$this->variableName,
				$analyserContext->programRegistry->typeRegistry->function(
					$v->type->parameterType,
					$v->type->returnType,
				)
			);
		}
		try {
			$ret = $this->assignedExpression->analyse($analyserContext);
			return $ret->withAddedVariableType(
				$this->variableName,
				$ret->expressionType
			);
		} catch (AnalyserException $e) {
			/** @noinspection PhpUnhandledExceptionInspection */
			throw $innerFn ? new AnalyserException(
				sprintf(
					"Error in function assigned to variable '%s': %s",
					$this->variableName,
					$e->getMessage()
				)
			) : $e;
		}
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$ret = $this->assignedExpression->execute($executionContext);
		$val = $ret->typedValue;
		if ($val->value instanceof FunctionValue && $this->assignedExpression instanceof ConstantExpression) {
			$val = TypedValue::forValue($val->value->withSelfReferenceAs($this->variableName));
		}
		return $ret->withAddedVariableValue(
			$this->variableName,
			$val
		);
	}

	public function __toString(): string {
		return sprintf(
			"%s = %s",
			$this->variableName,
			$this->assignedExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'variableAssignment',
			'variableName' => $this->variableName,
			'assignedExpression' => $this->assignedExpression
		];
	}
}