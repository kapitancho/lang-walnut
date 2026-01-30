<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;

interface ProgramBuilder {
	/** @throws ProgramCompilationException */
	public function compileProgram(RootNode $root): void;
}