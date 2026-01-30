<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;

interface ExpressionBuilder {
	/** @throws CompilationException */
	public function expression(ExpressionNode $expressionNode): Expression;
}