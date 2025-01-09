<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler;
use Walnut\Lang\Blueprint\Function\FunctionBody;

final readonly class AstGlobalFunctionBodyCompiler implements AstFunctionBodyCompiler {

	/** @throws AstCompilationException */
	public function functionBody(ExpressionNode $expressionNode): FunctionBody {
		throw new AstCompilationException(
			$expressionNode,
			"Functions in the global scope cannot be compiled directly."
		);
	}
}