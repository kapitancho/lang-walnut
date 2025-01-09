<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\Function\FunctionBody;

interface AstFunctionBodyCompiler {
	/** @throws AstCompilationException */
	public function functionBody(
		FunctionBodyNode $functionBodyNode
	): FunctionBody;
}