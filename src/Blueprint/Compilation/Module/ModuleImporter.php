<?php

namespace Walnut\Lang\Blueprint\Compilation\Module;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;

interface ModuleImporter {
	/** @throws ModuleDependencyException|ParserException */
	public function importModules(string $startModuleName): RootNode;
}