<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\MatchExpressionOperation;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class MatchExpressionEquals implements MatchExpressionOperation, JsonSerializable {

	public function match(Value $matchValue, Value $matchAgainst): bool {
		return $matchValue->equals($matchAgainst);
	}

	// @codeCoverageIgnoreStart
	public function __toString(): string {
		return "==";
	}
	// @codeCoverageIgnoreEnd

	public function jsonSerialize(): string {
		return 'equals';
	}
}