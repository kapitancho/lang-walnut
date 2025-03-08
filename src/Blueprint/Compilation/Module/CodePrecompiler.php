<?php

namespace Walnut\Lang\Blueprint\Compilation\Module;

interface CodePrecompiler {
	public function precompileSourceCode(string $moduleName, string $sourceCode): string;
}