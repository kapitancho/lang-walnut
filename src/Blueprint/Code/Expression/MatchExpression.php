<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface MatchExpression extends Expression {
	public Expression $target { get; }
	public MatchExpressionOperation $operation { get; }
	/** @var list<MatchExpressionPair|MatchExpressionDefault> */
	public array $pairs { get; }
}