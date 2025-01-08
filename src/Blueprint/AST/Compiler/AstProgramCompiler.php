<?php

namespace Walnut\Lang\Blueprint\AST\Compiler;

use Walnut\Lang\Blueprint\AST\Node\RootNode;

interface AstProgramCompiler {
	/** @throws AstProgramCompilationException */
	public function compileProgram(RootNode $root): void;
}