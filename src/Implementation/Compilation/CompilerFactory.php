<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Compilation\CompilerFactory as CompilerFactoryInterface;
use Walnut\Lang\Implementation\Compilation\Module\MultiFolderBasedModuleLookupContext;
use Walnut\Lang\Implementation\Compilation\Module\TemplatePrecompiler;
use Walnut\Lang\Implementation\Compilation\Module\TemplatePrecompilerModuleLookupDecorator;

final readonly class CompilerFactory implements CompilerFactoryInterface {

	public function compiler(
		string|null $templateSourceRoot,
		string ... $sourceRoots
	): Compiler {
		$lookupContext = new MultiFolderBasedModuleLookupContext(... $sourceRoots);
		if ($templateSourceRoot !== null) {
			$lookupContext = new TemplatePrecompilerModuleLookupDecorator(
				new TemplatePrecompiler(),
				$lookupContext,
				$templateSourceRoot
			);
		}
		return new Compiler($lookupContext);
	}
}