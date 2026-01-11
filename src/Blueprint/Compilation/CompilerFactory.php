<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\Compilation\AST\AstCodeMapper;
use Walnut\Lang\Blueprint\Compilation\Module\PackageConfigurationProvider;
use Walnut\Lang\Blueprint\Compilation\Module\SourceFinder;

interface CompilerFactory {
	public function defaultCompiler(
		PackageConfigurationProvider $packageConfigurationProvider,
		AstCodeMapper|null $astCodeMapper = null
	): Compiler;

	public function customCompiler(
		SourceFinder $sourceFinder,
		AstCodeMapper|null $astCodeMapper = null
	): Compiler;
}