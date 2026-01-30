<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;

interface ModuleBuilder {
	/** @throws ModuleCompilationException */
	public function compileModule(ModuleNode $module): void;
}