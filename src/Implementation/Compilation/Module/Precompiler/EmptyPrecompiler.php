<?php

namespace Walnut\Lang\Implementation\Compilation\Module\Precompiler;

use Walnut\Lang\Blueprint\Compilation\Module\CodePrecompiler;

final readonly class EmptyPrecompiler implements CodePrecompiler {

	public function determineSourcePath(string $sourcePath): string|null {
		return $sourcePath . '.nut';
	}

	public function precompileSourceCode(string $moduleName, string $sourceCode): string {
		return $sourceCode;
	}
}