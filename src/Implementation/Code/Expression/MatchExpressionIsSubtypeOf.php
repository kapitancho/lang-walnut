<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionOperation;
use Walnut\Lang\Blueprint\Value\SubtypeValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class MatchExpressionIsSubtypeOf implements MatchExpressionOperation, JsonSerializable {

	public function match(Value $matchValue, Value $matchAgainst): bool {
		return
			($matchAgainst instanceof TypeValue) && (
				$matchValue->type()->isSubtypeOf($matchAgainst->typeValue()) ||
				($matchValue instanceof SubtypeValue && $this->match($matchValue->baseValue(), $matchAgainst))
			);
	}

	public function __toString(): string {
		return "<:";
	}

	public function jsonSerialize(): string {
		return 'isSubtypeOf';
	}
}