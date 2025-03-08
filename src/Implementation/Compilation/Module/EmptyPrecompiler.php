<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use Walnut\Lang\Blueprint\Compilation\Module\CodePrecompiler;

final readonly class EmptyPrecompiler implements CodePrecompiler {
	public function precompileSourceCode(string $moduleName, string $sourceCode): string {
		return $sourceCode;
	}
}