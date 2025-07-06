<?php

namespace Walnut\Lang\Blueprint\Compilation\Module;

interface CodePrecompiler {
	public function determineSourcePath(string $sourcePath): string|null;
	public function precompileSourceCode(string $moduleName, string $sourceCode): string;
}