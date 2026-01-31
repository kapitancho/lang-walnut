<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;

interface ExpressionBuilder {
	/** @throws BuildException */
	public function expression(ExpressionNode $expressionNode): Expression;
}