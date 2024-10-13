<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair as MatchExpressionPairInterface;

final readonly class MatchExpressionPair implements MatchExpressionPairInterface {

	public function __construct(
		private Expression $matchExpression,
		private Expression $valueExpression
	) {}

	public function __toString(): string {
		return sprintf("~: %s", $this->valueExpression);
	}

	public function matchExpression(): Expression {
		return $this->matchExpression;
	}

	public function valueExpression(): Expression {
		return $this->valueExpression;
	}
}