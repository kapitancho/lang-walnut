<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\ConstantExpression;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\VariableAssignmentExpression as VariableAssignmentExpressionInterface;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Value\FunctionValue;

final readonly class VariableAssignmentExpression implements VariableAssignmentExpressionInterface, JsonSerializable {
	public function __construct(
		public VariableNameIdentifier $variableName,
		public Expression $assignedExpression
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		if ($this->assignedExpression instanceof ConstantExpression &&
			($v = $this->assignedExpression->value) instanceof FunctionValue
		) {
			$analyserContext = $analyserContext->withAddedVariableType(
				$this->variableName,
				$analyserContext->typeRegistry->function(
					$v->type->parameterType,
					$v->type->returnType,
				)
			);
		}
		$ret = $this->assignedExpression->analyse($analyserContext);
		return $ret->withAddedVariableType(
			$this->variableName,
			$ret->expressionType
		);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return $this->assignedExpression->analyseDependencyType($dependencyContainer);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$ret = $this->assignedExpression->execute($executionContext);
		$val = $ret->value;
		if ($val instanceof FunctionValue && $this->assignedExpression instanceof ConstantExpression) {
			$val = $val->withSelfReferenceAs($this->variableName);
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