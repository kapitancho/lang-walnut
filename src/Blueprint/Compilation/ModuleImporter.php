<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;

interface ModuleImporter {
	public function importModules(string $startModuleName): RootNode;
}