<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\TupleExpression as TupleExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;

final readonly class TupleExpression implements TupleExpressionInterface, JsonSerializable {

	/** @param list<Expression> $values */
	public function __construct(
		public array $values
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$subtypes = [];
		$returnTypes = [];
		foreach($this->values as $value) {
			$analyserContext = $value->analyse($analyserContext);
			$subtypes[] = $analyserContext->expressionType;
			$returnTypes[] = $analyserContext->returnType;
		}
		return $analyserContext->asAnalyserResult(
			$analyserContext->programRegistry->typeRegistry->tuple($subtypes),
			$analyserContext->programRegistry->typeRegistry->union($returnTypes)
		);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return array_merge(... array_map(
			fn(Expression $expression) => $expression->analyseDependencyType($dependencyContainer),
			$this->values
		));
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$values = [];
		foreach($this->values as $value) {
			$executionContext = $value->execute($executionContext);
			$values[] = $executionContext->value;
		}
		return $executionContext->asExecutionResult(
			(
				$executionContext->programRegistry->valueRegistry->tuple($values)
			)
		);
	}

	public function __toString(): string {
		return sprintf(
			"[%s]",
			implode(", ", $this->values)
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Tuple',
			'values' => $this->values
		];
	}
}