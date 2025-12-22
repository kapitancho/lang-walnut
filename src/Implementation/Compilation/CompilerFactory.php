<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Compilation\CompilerFactory as CompilerFactoryInterface;
use Walnut\Lang\Blueprint\Compilation\Module\PackageConfigurationProvider;
use Walnut\Lang\Blueprint\Compilation\Module\SourceFinder;
use Walnut\Lang\Implementation\AST\Parser\StringEscapeCharHandler;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\EmptyPrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\TemplatePrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\TestPrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\PrecompilerModuleLookupContext;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\PackageBasedSourceFinder;

final readonly class CompilerFactory implements CompilerFactoryInterface {

	public function defaultCompiler(
		PackageConfigurationProvider $packageConfigurationProvider
	): Compiler {
		return $this->customCompiler(
			new PackageBasedSourceFinder($packageConfigurationProvider)
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
				new TemplatePrecompiler(new StringEscapeCharHandler()),
			]
		);
		return new Compiler($lookupContext);
	}
}