<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Compilation\CompilerFactory as CompilerFactoryInterface;
use Walnut\Lang\Implementation\Compilation\Module\EmptyPrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\PackageBasedModuleLookupContext;
use Walnut\Lang\Implementation\Compilation\Module\PackageBasedModulePathFinder;
use Walnut\Lang\Implementation\Compilation\Module\TemplatePrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\TestPrecompiler;

final readonly class CompilerFactory implements CompilerFactoryInterface {

	/** @param array<string, string> $packageRoots */
	public function compiler(
		string $defaultRoot,
		array $packageRoots,
	): Compiler {
		$modulePathFinder = new PackageBasedModulePathFinder(
			$defaultRoot,
			$packageRoots
		);
		$lookupContext = new PackageBasedModuleLookupContext(
			$modulePathFinder,
			[
				new TestPrecompiler(),
				new EmptyPrecompiler(),
				new TemplatePrecompiler(),
			]
		);
		return new Compiler($lookupContext);
	}
}