<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

final readonly class SequenceExpression extends SequentialExpressionBase {

	/** @param array<Type> $expressionTypes */
	protected function buildExpressionType(array $expressionTypes, int $set, int $dynamic): Type {
		return array_last($expressionTypes);
	}

	/** @param array<Value> $expressionValues */
	protected function buildExpressionValue(array $expressionValues): Value {
		return array_last($expressionValues);
	}

	public function __toString(): string {
		return count($this->expressions) > 1 ?
			sprintf("{%s}", implode("; ", $this->expressions)) :
			(string)($this->expressions[0] ?? "");
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Sequence',
			'expressions' => $this->expressions
		];
	}
}