<?php

namespace Walnut\Lang\Almond\Source\Implementation\Precompiler;

use Walnut\Lang\Almond\Source\Blueprint\Precomipiler\CodePrecompiler;

final readonly class EmptyPrecompiler implements CodePrecompiler {

	public function determineSourcePath(string $sourcePath): string {
		return $sourcePath . '.nut';
	}

	public function precompileSourceCode(string $moduleName, string $sourceCode): string {
		return $sourceCode;
	}
}