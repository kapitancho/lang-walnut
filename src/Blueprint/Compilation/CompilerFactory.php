<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\Compilation\Module\PackageConfigurationProvider;
use Walnut\Lang\Blueprint\Compilation\Module\SourceFinder;
use Walnut\Lang\Implementation\Compilation\Compiler;

interface CompilerFactory {
	public function compiler(PackageConfigurationProvider $packageConfigurationProvider): Compiler;

	public function defaultCompiler(PackageConfigurationProvider $packageConfigurationProvider): Compiler;

	public function customCompiler(
		SourceFinder $sourceFinder
	): Compiler;
}