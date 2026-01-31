<?php

namespace Walnut\Lang\Almond\Source\Blueprint\Precompiler;

interface CodePrecompiler {
	public function determineSourcePath(string $sourcePath): string|null;
	public function precompileSourceCode(string $moduleName, string $sourceCode): string;
}