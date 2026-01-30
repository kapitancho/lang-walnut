<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class RecordExpression extends SequentialExpressionBase {

	/** @param array<Type> $expressionTypes */
	protected function buildExpressionType(array $expressionTypes, int $set, int $dynamic): Type {
		return $this->typeRegistry->record($expressionTypes, null);
	}

	/** @param array<Value> $expressionValues */
	protected function buildExpressionValue(array $expressionValues): Value {
		return $this->valueRegistry->record($expressionValues);
	}


	public function __toString(): string {
		$values = [];
		foreach($this->expressions as $key => $type) {
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
			'values' => $this->expressions
		];
	}
}