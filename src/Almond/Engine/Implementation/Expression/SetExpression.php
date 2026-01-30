<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class SetExpression extends SequentialExpressionBase {

	/** @param array<Type> $expressionTypes */
	protected function buildExpressionType(array $expressionTypes, int $set, int $dynamic): Type {
		return $this->typeRegistry->set(
			$this->typeRegistry->union($expressionTypes),
			min(max(1, $set), count($this->expressions)),
			$set + $dynamic
		);
	}

	/** @param array<Value> $expressionValues */
	protected function buildExpressionValue(array $expressionValues): Value {
		return $this->valueRegistry->set($expressionValues);
	}

	public function __toString(): string {
		return match(count($this->expressions)) {
			0 => '[;]',
			1 => sprintf(
				"[%s;]",
				$this->expressions[0]
			),
			default => sprintf(
				"[%s]",
				implode("; ", $this->expressions)
			)
		};
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Set',
			'expressions' => $this->expressions
		];
	}
}