<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MultiVariableAssignmentExpression as MultiVariableAssignmentExpressionInterface;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;

final readonly class MultiVariableAssignmentExpression implements MultiVariableAssignmentExpressionInterface, JsonSerializable {
	/** @param array<VariableNameIdentifier> $variableNames */
	public function __construct(
		public array $variableNames,
		public Expression $assignedExpression
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$methodName = new MethodNameIdentifier('item');
		$ret = $this->assignedExpression->analyse($analyserContext);
		$retType = $ret->expressionType;
		$isList = array_is_list($this->variableNames);
		foreach ($this->variableNames as $key => $variableName) {
			$ret = $ret->withAddedVariableType(
				$variableName,
				$analyserContext->methodAnalyser->analyseMethod(
					$retType,
					$methodName,
					($isList ?
						$analyserContext->typeRegistry->integerSubset([new Number($key)]) :
						$analyserContext->typeRegistry->stringSubset([$key])
					)
				)
			);
		}
		return $ret;
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return $this->assignedExpression->analyseDependencyType($dependencyContainer);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$methodName = new MethodNameIdentifier('item');
		$ret = $this->assignedExpression->execute($executionContext);
		$val = $ret->value;
		$isList = array_is_list($this->variableNames);

		foreach ($this->variableNames as $key => $variableName) {
			$ret = $ret->withAddedVariableValue(
				$variableName,
				$executionContext->methodContext->executeMethod(
					$val,
					$methodName,
					($isList ?
						$executionContext->valueRegistry->integer($key) :
						$executionContext->valueRegistry->string($key)
					)
				)
			);
		}

		return $ret;
	}

	public function __toString(): string {
		$variableNames = [];
		if (array_is_list($this->variableNames)) {
			foreach($this->variableNames as $variableName) {
				$variableNames[] = $variableName;
			}
		} else {
			foreach($this->variableNames as $key => $variableName) {
				$variableNames[] = $variableName->identifier === $key ?
					"~$key" : "$key: $variableName";
			}
		}
		return sprintf(
			"var{%s} = %s",
			implode(', ', $variableNames),
			$this->assignedExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'multiVariableAssignment',
			'variableNames' => $this->variableNames,
			'assignedExpression' => $this->assignedExpression
		];
	}
}