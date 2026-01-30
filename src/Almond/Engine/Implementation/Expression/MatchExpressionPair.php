<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;


use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\MatchExpressionPair as MatchExpressionPairInterface;

final readonly class MatchExpressionPair implements MatchExpressionPairInterface {

	public function __construct(
		public Expression $matchExpression,
		public Expression $valueExpression
	) {}

	public function __toString(): string {
		return sprintf("%s: %s", $this->matchExpression, $this->valueExpression);
	}
}