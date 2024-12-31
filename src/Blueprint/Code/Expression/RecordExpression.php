<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface RecordExpression extends Expression {
	/** @param array<string, Expression> $values */
	public array $values { get; }
}