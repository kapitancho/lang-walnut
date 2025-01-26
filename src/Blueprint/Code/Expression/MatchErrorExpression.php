<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface MatchErrorExpression extends Expression {
	public Expression $target { get; }
	public Expression $onError { get; }
	public Expression|null $else { get; }
}