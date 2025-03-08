<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Implementation\Compilation\Compiler;

interface CompilerFactory {
	/** @param array<string, string> $packageRoots */
	public function compiler(
		string $defaultRoot,
		array $packageRoots,
	): Compiler;
}