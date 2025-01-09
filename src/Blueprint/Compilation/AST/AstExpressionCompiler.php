<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\Code\Expression\Expression;

interface AstExpressionCompiler {
	/** @throws AstCompilationException */
	public function expression(ExpressionNode $expressionNode): Expression;
}