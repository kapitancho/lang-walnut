<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\ConstantExpression as ConstantExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class ConstantExpression implements ConstantExpressionInterface, JsonSerializable {
	public function __construct(
		public Value $value
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$this->value->selfAnalyse($analyserContext);
		return $analyserContext->asAnalyserResult(
			$this->value->type,
			$analyserContext->programRegistry->typeRegistry->nothing
		);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		$analyseErrors = [];
		if ($this->value instanceof FunctionValue) {
			$d = $this->value->function->dependencyType;
			if (!($d instanceof NothingType)) {
				$value = $dependencyContainer->valueByType($d);
				if ($value instanceof DependencyError) {
					$analyseErrors[] = sprintf("Error in %s: the dependency %s cannot be resolved: %s (type: %s)",
						$this->value->function->displayName,
						$d,
						$value->unresolvableDependency->errorInfo(),
						$value->type
					);
				}
			}
		}
		return $analyseErrors;
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