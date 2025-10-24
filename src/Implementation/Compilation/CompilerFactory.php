<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Compilation\CompilerFactory as CompilerFactoryInterface;
use Walnut\Lang\Blueprint\Compilation\Module\SourceFinder;
use Walnut\Lang\Implementation\AST\Parser\EscapeCharHandler;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\EmptyPrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\TemplatePrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\TestPrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\PrecompilerModuleLookupContext;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\PackageBasedSourceFinder;

final readonly class CompilerFactory implements CompilerFactoryInterface {

	/** @param array<string, string> $packageRoots */
	public function compiler(
		string $defaultRoot,
		array $packageRoots,
	): Compiler {
		return $this->customCompiler(
			new PackageBasedSourceFinder(
				$defaultRoot,
				$packageRoots
			)
		);
	}

	/** @param array<string, string> $packageRoots */
	public function defaultCompiler(
		string $defaultRoot,
		array $packageRoots,
	): Compiler {
		return $this->customCompiler(
			new PackageBasedSourceFinder(
				$defaultRoot,
				$packageRoots
			)
		);
	}

	public function customCompiler(
		SourceFinder $sourceFinder
	): Compiler {
		$lookupContext = new PrecompilerModuleLookupContext(
			$sourceFinder,
			[
				new TestPrecompiler(),
				new EmptyPrecompiler(),
				new TemplatePrecompiler(new EscapeCharHandler()),
			]
		);
		return new Compiler($lookupContext);
	}
}