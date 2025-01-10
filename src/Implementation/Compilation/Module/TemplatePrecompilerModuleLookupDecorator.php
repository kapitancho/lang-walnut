<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;

final readonly class TemplatePrecompilerModuleLookupDecorator implements ModuleLookupContext {
	public function __construct(
		private TemplatePrecompiler $templatePrecompiler,
		private ModuleLookupContext $moduleLookupContext,
		private string $templateSourceRoot
	) {}

	private function pathOf(string $moduleName): string {
		return $this->templateSourceRoot . '/' . str_replace('\\', '/', $moduleName) . '.nut.html';
	}

	/** @throws ModuleDependencyException */
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