<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Compilation\ModuleLookupContext;

final readonly class TemplatePrecompilerModuleLookupDecorator implements ModuleLookupContext {
	public function __construct(
		private TemplatePrecompiler $templatePrecompiler,
		private ModuleLookupContext $moduleLookupContext,
		private string $templateSourceRoot
	) {}

	private function pathOf(string $moduleName): string {
		return $this->templateSourceRoot . '/' . str_replace('\\', '/', $moduleName) . '.nut.html';
	}

	public function sourceOf(string $moduleName): string {
		$sourcePath = $this->pathOf($moduleName);
		if (file_exists($sourcePath) && is_readable($sourcePath)) {
			return $this->templatePrecompiler->precompileSourceCode(
				$moduleName,
				file_get_contents($sourcePath)
			);
		}
		return $this->moduleLookupContext->sourceOf($moduleName);
	}
}