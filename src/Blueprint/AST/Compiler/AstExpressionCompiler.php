<?php

namespace Walnut\Lang\Blueprint\AST\Compiler;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Function\FunctionBody;

interface AstExpressionCompiler {
	/** @throws AstCompilationException */
	public function expression(ExpressionNode $expressionNode): Expression;
}