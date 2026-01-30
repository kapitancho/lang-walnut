<?php

namespace Walnut\Lang\Almond\Source\Blueprint\Precomipiler;

interface CodePrecompiler {
	public function determineSourcePath(string $sourcePath): string|null;
	public function precompileSourceCode(string $moduleName, string $sourceCode): string;
}