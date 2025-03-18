<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\SetExpression as SetExpressionInterface;

final readonly class SetExpression implements SetExpressionInterface, JsonSerializable {

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
		$subtype = $analyserContext->programRegistry->typeRegistry->union($subtypes);
		return $analyserContext->asAnalyserResult(
			$analyserContext->programRegistry->typeRegistry->set(
				$subtype,
				min(1, count($this->values)),
				count($this->values)
			),
			$analyserContext->programRegistry->typeRegistry->union($returnTypes)
		);
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