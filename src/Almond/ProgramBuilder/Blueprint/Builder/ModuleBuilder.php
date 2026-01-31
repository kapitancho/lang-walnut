<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;

interface ModuleBuilder {
	/** @throws BuildException */
	public function compileModule(ModuleNode $module): void;
}