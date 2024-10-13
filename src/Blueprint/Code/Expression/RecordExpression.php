<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface RecordExpression extends Expression {
	/** @return array<string, Expression> */
	public function values(): array;
}