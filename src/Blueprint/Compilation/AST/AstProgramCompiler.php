<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\RootNode;

interface AstProgramCompiler {
	/** @throws AstProgramCompilationException */
	public function compileProgram(RootNode $root): void;
}