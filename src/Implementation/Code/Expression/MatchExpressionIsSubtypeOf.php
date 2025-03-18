<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionOperation;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;

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