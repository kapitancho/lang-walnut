<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

final readonly class SetExpression extends SequentialExpressionBase {

	/** @param array<Type> $expressionTypes */
	protected function buildExpressionType(array $expressionTypes, int $set, int $dynamic): Type {
		$hasOptional = false;
		$setItemType = $this->typeRegistry->union($expressionTypes);
		if ($setItemType instanceof OptionalType) {
			$hasOptional = true;
			$setItemType = $setItemType->valueType;
		}
		return $this->typeRegistry->set(
			$setItemType,
			$hasOptional ? 0 : min(max(1, $set), count($this->expressions)),
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