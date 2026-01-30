<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\MatchExpressionOperation;
use Walnut\Lang\Almond\Engine\Blueprint\Value\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class MatchExpressionIsSubtypeOf implements MatchExpressionOperation, JsonSerializable {

	public function match(Value $matchValue, Value $matchAgainst): bool {
		return ($matchAgainst instanceof TypeValue) &&
			$matchValue->type->isSubtypeOf($matchAgainst->typeValue);
	}

	// @codeCoverageIgnoreStart
	public function __toString(): string {
		return "<:";
	}
	// @codeCoverageIgnoreEnd

	public function jsonSerialize(): string {
		return 'isSubtypeOf';
	}
}