<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;

interface FunctionBodyBuilder {
	/** @throws CompilationException */
	public function functionBody(
		FunctionBodyNode $functionBodyNode
	): FunctionBody;

	/** @throws CompilationException */
	public function validatorBody(
		FunctionBodyNode $functionBodyNode
	): FunctionBody;
}