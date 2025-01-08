<?php

namespace Walnut\Lang\Blueprint\AST\Compiler;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft;

interface AstFunctionBodyCompiler {
	/** @throws AstCompilationException */
	public function functionBody(
		FunctionBodyNode|FunctionBodyDraft $functionBody
	): FunctionBody;
}