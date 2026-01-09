<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\VariableNameExpression as VariableNameExpressionInterface;
use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;

final readonly class VariableNameExpression implements VariableNameExpressionInterface, JsonSerializable {
	public function __construct(
		public VariableNameIdentifier $variableName
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		try {
			$type = $analyserContext->variableScope->typeOf($this->variableName);
		} catch (UnknownContextVariable) {
			throw new AnalyserException(
				sprintf(
					"Unknown variable '%s'",
					$this->variableName
				),
				$this
			);
		}
		return $analyserContext->asAnalyserResult($type, $analyserContext->typeRegistry->nothing);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return [];
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