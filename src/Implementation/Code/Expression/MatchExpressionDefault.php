<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault as MatchExpressionDefaultInterface;

final readonly class MatchExpressionDefault implements MatchExpressionDefaultInterface {

	public function __construct(
		private Expression $valueExpression
	) {}

	public function __toString(): string {
		return sprintf("~: %s", $this->valueExpression);
	}

	public function valueExpression(): Expression {
		return $this->valueExpression;
	}
}