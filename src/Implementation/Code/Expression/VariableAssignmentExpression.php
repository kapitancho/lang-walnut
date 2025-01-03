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
use Walnut\Lang\Blueprint\Function\FunctionBodyException;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Value\FunctionValue;

final readonly class VariableAssignmentExpression implements VariableAssignmentExpressionInterface, JsonSerializable {
	public function __construct(
		private TypeRegistry $typeRegistry,
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
				$this->typeRegistry->function(
					$v->parameterType,
					$v->returnType,
				)
			);
		}
		try {
			$ret = $this->assignedExpression->analyse($analyserContext);
			return $ret->withAddedVariableType(
				$this->variableName,
				$ret->expressionType
			);
		} catch (AnalyserException|FunctionBodyException $e) {
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
			$val = new TypedValue($val->type, $val->value->withSelfReferenceAs($this->variableName));
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