<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;

interface AstModuleCompiler {
	/** @throws AstModuleCompilationException */
	public function compileModule(ModuleNode $module): void;
}