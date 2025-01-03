<?php

namespace Walnut\Lang\Blueprint\AST\Compiler;

use Walnut\Lang\Blueprint\AST\Node\RootNode;

interface AstCompiler {
	/** @throws AstProgramCompilationException */
	public function compile(RootNode $root): void;
}