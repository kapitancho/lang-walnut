<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionOperation;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class MatchExpressionEquals implements MatchExpressionOperation, JsonSerializable {

	public function match(Value $matchValue, Value $matchAgainst): bool {
		/*if (!$result && $matchValue instanceof SubtypeValue) {
			return $this->match($matchValue->baseValue(), $matchAgainst);
		}*/
		return $matchValue->equals($matchAgainst);
	}

	public function __toString(): string {
		return "==";
	}

	public function jsonSerialize(): string {
		return 'equals';
	}
}