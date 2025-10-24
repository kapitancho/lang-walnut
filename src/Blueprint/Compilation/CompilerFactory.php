<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\Compilation\Module\SourceFinder;
use Walnut\Lang\Implementation\Compilation\Compiler;

interface CompilerFactory {
	/** @param array<string, string> $packageRoots */
	public function compiler(
		string $defaultRoot,
		array $packageRoots,
	): Compiler;

	/** @param array<string, string> $packageRoots */
	public function defaultCompiler(
		string $defaultRoot,
		array $packageRoots,
	): Compiler;

	public function customCompiler(
		SourceFinder $sourceFinder
	): Compiler;
}