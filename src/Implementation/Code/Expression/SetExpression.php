<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\ConstantExpression;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\SetExpression as SetExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;

final readonly class SetExpression implements SetExpressionInterface, JsonSerializable {

	/** @param list<Expression> $values */
	public function __construct(
		public array $values
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$subtypes = [];
		$returnTypes = [];
		$set = [];
		$dynamic = 0;
		foreach($this->values as $value) {
			if ($value instanceof ConstantExpression) {
				$set[(string)$value] = true;
			} else {
				$dynamic++;
			}
			$analyserContext = $value->analyse($analyserContext);
			$subtypes[] = $analyserContext->expressionType;
			$returnTypes[] = $analyserContext->returnType;
		}
		$subtype = $analyserContext->programRegistry->typeRegistry->union($subtypes);

		return $analyserContext->asAnalyserResult(
			$analyserContext->programRegistry->typeRegistry->set(
				$subtype,
				min(max(1, count($set)), count($this->values)),
				count($set) + $dynamic
			),
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
		return $executionContext->asExecutionResult((
			$executionContext->programRegistry->valueRegistry->set($values)
		));
	}

	public function __toString(): string {
		return match(count($this->values)) {
			0 => '[;]',
			1 => sprintf(
				"[%s;]",
				$this->values[0]
			),
			default => sprintf(
				"[%s]",
				implode("; ", $this->values)
			)
		};
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Set',
			'values' => $this->values
		];
	}
}