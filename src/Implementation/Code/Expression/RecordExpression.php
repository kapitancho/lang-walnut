<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\RecordExpression as RecordExpressionInterface;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

final readonly class RecordExpression implements RecordExpressionInterface, JsonSerializable {

	/** @param array<string, Expression> $values */
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private array $values
	) {}

	/** @return array<string, Expression> */
	public function values(): array {
		return $this->values;
	}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$subtypes = [];
		$returnTypes = [];
		foreach($this->values as $key => $value) {
			$analyserContext = $value->analyse($analyserContext);
			$subtypes[$key] = $analyserContext->expressionType();
			$returnTypes[] = $analyserContext->returnType();
		}
		return $analyserContext->asAnalyserResult(
			$this->typeRegistry->record($subtypes),
			$this->typeRegistry->union($returnTypes),
		);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$values = [];
		$types = [];
		foreach($this->values as $key => $value) {
			$executionContext = $value->execute($executionContext);
			$values[$key] = $executionContext->value();
			$types[$key] = $executionContext->valueType();
		}
		return $executionContext->asExecutionResult(new TypedValue(
			$this->typeRegistry->record($types),
			$this->valueRegistry->record($values)
		));
	}

	public function __toString(): string {
		$values = [];
		foreach($this->values as $key => $type) {
			$values[] = "$key: $type";
		}
		return count($values) ? sprintf(
			"[%s]",
			implode(", ", $values)
		) : '[:]';
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Record',
			'values' => $this->values
		];
	}
}