<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;


use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\MatchExpressionDefault as MatchExpressionDefaultInterface;

final readonly class MatchExpressionDefault implements MatchExpressionDefaultInterface {
	public function __construct(
		public Expression $valueExpression
	) {}

	public function __toString(): string {
		return sprintf("~: %s", $this->valueExpression);
	}
}