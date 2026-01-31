<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;

interface ProgramBuilder {
	/** @throws BuildException */
	public function compileProgram(RootNode $root): void;
}