<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionOperation;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class MatchExpressionIsSubtypeOf implements MatchExpressionOperation, JsonSerializable {

	public function match(TypedValue $matchValue, TypedValue $matchAgainst): bool {
		return ($matchAgainst->value instanceof TypeValue) &&
			$matchValue->isSubtypeOf($matchAgainst->value->typeValue);
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