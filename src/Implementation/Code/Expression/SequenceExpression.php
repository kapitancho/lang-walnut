<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\SequenceExpression as SequenceExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;

final readonly class SequenceExpression implements SequenceExpressionInterface, JsonSerializable {

	/** @param list<Expression> $expressions */
	public function __construct(
		public array $expressions
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$expressionType = $analyserContext->programRegistry->typeRegistry->nothing;
		$returnTypes = [];
		foreach($this->expressions as $expression) {
			$analyserContext = $expression->analyse($analyserContext);

			$expressionType = $analyserContext->expressionType;
			$returnTypes[] = $analyserContext->returnType;
			if ($expression instanceof ReturnExpression) {
				break;
			}
		}
		return $analyserContext->asAnalyserResult(
			$expressionType,
			$analyserContext->programRegistry->typeRegistry->union($returnTypes),
		);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return array_merge(... array_map(
			fn(Expression $expression) => $expression->analyseDependencyType($dependencyContainer),
			$this->expressions
		));
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$result = ($executionContext->programRegistry->valueRegistry->null);
		foreach($this->expressions as $expression) {
			$executionContext = $expression->execute($executionContext);
			$result = $executionContext->value;
		}
		return $executionContext->asExecutionResult($result);
	}

	public function __toString(): string {
		return count($this->expressions) > 1 ?
			sprintf("{%s}", implode("; ", $this->expressions)) :
			(string)($this->expressions[0] ?? "");
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'sequence',
			'expressions' => $this->expressions
		];
	}
}