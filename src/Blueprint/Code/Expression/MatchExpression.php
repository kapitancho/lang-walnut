<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface MatchExpression extends Expression {
	public function target(): Expression;
	public function operation(): MatchExpressionOperation;
	/** @return list<MatchExpressionPair> */
	public function pairs(): array;
}