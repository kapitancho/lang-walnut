<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Parser;

interface ModuleContentProvider {
	public function contentOf(string $moduleName): string|null;
}