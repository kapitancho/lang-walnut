<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;

final readonly class MultiFolderBasedModuleLookupContext implements ModuleLookupContext {

	private array $sourceRoots;

	public function __construct(
		string ... $sourceRoots
	) {
		$this->sourceRoots = $sourceRoots;
	}

	private function pathOf(string $sourceRoot, string $moduleName): string {
		return $sourceRoot . '/' . str_replace('\\', '/', $moduleName) . '.nut';
	}

	/** @throws ModuleDependencyException */
	public function sourceOf(string $moduleName): string {
		foreach($this->sourceRoots as $sourceRoot) {
			$sourcePath = $this->pathOf($sourceRoot, $moduleName);
			if(file_exists($sourcePath) && is_readable($sourcePath)) {
				return file_get_contents($sourcePath);
			}
		}
		throw new ModuleDependencyException($moduleName);
	}
}