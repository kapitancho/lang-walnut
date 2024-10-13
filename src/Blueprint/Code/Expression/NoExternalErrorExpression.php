<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface NoExternalErrorExpression extends Expression {
	public function targetExpression(): Expression;
}