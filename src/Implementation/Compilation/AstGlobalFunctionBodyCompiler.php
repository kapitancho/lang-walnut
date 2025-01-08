<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Compiler\AstCompilationException;
use Walnut\Lang\Blueprint\AST\Compiler\AstFunctionBodyCompiler;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft;

final readonly class AstGlobalFunctionBodyCompiler implements AstFunctionBodyCompiler {

	/** @throws AstCompilationException */
	public function functionBody(FunctionBodyNode|FunctionBodyDraft $functionBody): FunctionBody {
		throw new AstCompilationException(
			$functionBody,
			"Functions in the global scope cannot be compiled directly."
		);
	}
}