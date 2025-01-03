<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Compilation\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\ModuleLookupContext;

final readonly class FolderBasedModuleLookupContext implements ModuleLookupContext {

	public function __construct(
		private string $sourceRoot
	) {}

	private function pathOf(string $moduleName): string {
		return $this->sourceRoot . '/' . str_replace('\\', '/', $moduleName) . '.nut';
	}

	/** @throws ModuleDependencyException */
	public function sourceOf(string $moduleName): string {
		$sourcePath = $this->pathOf($moduleName);
		if(!file_exists($sourcePath) || !is_readable($sourcePath)) {
			throw new ModuleDependencyException($moduleName);
		}
		return file_get_contents($sourcePath);
	}
}