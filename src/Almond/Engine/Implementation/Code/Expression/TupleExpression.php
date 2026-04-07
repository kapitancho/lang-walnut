<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EmptyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

final readonly class TupleExpression extends SequentialExpressionBase {

	/** @param array<Type> $expressionTypes */
	protected function buildExpressionType(array $expressionTypes, int $set, int $dynamic): Type {
		$empty = 0;
		$optional = 0;
		foreach ($expressionTypes as $t) {
			if ($t instanceof OptionalType) {
				$optional++;
				if ($t instanceof EmptyType) {
					$empty++;
				}
			}
		}
		if ($optional > 0) {
			$itemType = $this->typeRegistry->union($expressionTypes);
			if ($itemType instanceof OptionalType) {
				return $this->typeRegistry->array(
					$itemType->valueType,
					count($expressionTypes) - $optional,
					count($expressionTypes) - $empty,
				);
			}
		}
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