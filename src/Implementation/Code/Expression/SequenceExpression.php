<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\SequenceExpression as SequenceExpressionInterface;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

final readonly class SequenceExpression implements SequenceExpressionInterface, JsonSerializable {

	/** @param list<Expression> $expressions */
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private array $expressions
	) {}

	/** @return list<Expression> */
	public function expressions(): array {
		return $this->expressions;
	}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$expressionType = $this->typeRegistry->nothing();
		$returnTypes = [];
		foreach($this->expressions as $expression) {
			$analyserContext = $expression->analyse($analyserContext);

			$expressionType = $analyserContext->expressionType();
			$returnTypes[] = $analyserContext->returnType();
			if ($expression instanceof ReturnExpression) {
				break;
			}
		}
		return $analyserContext->asAnalyserResult(
			$expressionType,
			$this->typeRegistry->union($returnTypes),
		);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$result = TypedValue::forValue($this->valueRegistry->null());
		foreach($this->expressions as $expression) {
			$executionContext = $expression->execute($executionContext);
			$result = $executionContext->typedValue();
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