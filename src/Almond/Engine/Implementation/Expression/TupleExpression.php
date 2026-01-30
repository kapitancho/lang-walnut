<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class TupleExpression extends SequentialExpressionBase {

	/** @param array<Type> $expressionTypes */
	protected function buildExpressionType(array $expressionTypes, int $set, int $dynamic): Type {
		return $this->typeRegistry->tuple($expressionTypes, null);
	}

	/** @param array<Value> $expressionValues */
	protected function buildExpressionValue(array $expressionValues): Value {
		return $this->valueRegistry->tuple($expressionValues);
	}

	public function __toString(): string {
		return sprintf(
			"[%s]",
			implode(", ", $this->expressions)
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Tuple',
			'values' => $this->expressions
		];
	}
}