<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;

interface FunctionBodyBuilder {
	/** @throws BuildException */
	public function functionBody(
		FunctionBodyNode $functionBodyNode
	): FunctionBody;
}