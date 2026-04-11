<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope;

interface ValidationContextScope {
	public function typeOf(Expression|FunctionBody $expression): Type|null;
	public function scopeAt(Expression|FunctionBody $expression): VariableScope|null;
}