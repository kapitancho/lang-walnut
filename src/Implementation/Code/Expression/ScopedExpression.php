<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\ScopedExpression as ScopedExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Value\ErrorValue;

final readonly class ScopedExpression implements ScopedExpressionInterface, JsonSerializable {
	public function __construct(
		public Expression $targetExpression
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$ret = $this->targetExpression->analyse($analyserContext);
		$expressionType = $ret->expressionType;
		$returnType = $ret->returnType;
		return $analyserContext->asAnalyserResult(
			$analyserContext->programRegistry->typeRegistry->result(
				$expressionType,
				$returnType
			),
			$analyserContext->programRegistry->typeRegistry->nothing
		);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return $this->targetExpression->analyseDependencyType($dependencyContainer);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		try {
			$result = $this->targetExpression->execute($executionContext)->value;
		} catch (FunctionReturn $return) {
			$result = $return->typedValue;
		}
		return $executionContext->asExecutionResult($result);
	}

	public function __toString(): string {
		return sprintf(
			":: %s",
			$this->targetExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Scoped',
			'targetExpression' => $this->targetExpression
		];
	}
}