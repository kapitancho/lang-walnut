<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\ReturnExpression as ReturnExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;

final readonly class ReturnExpression implements ReturnExpressionInterface, JsonSerializable {
	public function __construct(
		public Expression $returnedExpression
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$ret = $this->returnedExpression->analyse($analyserContext);
		return $ret->withExpressionType(
			$analyserContext->programRegistry->typeRegistry->nothing
		)->withReturnType($analyserContext->programRegistry->typeRegistry->union(
			[$ret->returnType, $ret->expressionType]
		));
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return $this->returnedExpression->analyseDependencyType($dependencyContainer);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		throw new FunctionReturn(
			$this->returnedExpression->execute($executionContext)->value
		);
	}

	public function __toString(): string {
		return sprintf(
			"=> %s",
			$this->returnedExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'return',
			'returnedExpression' => $this->returnedExpression
		];
	}
}