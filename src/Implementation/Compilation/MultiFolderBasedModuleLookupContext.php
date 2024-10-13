<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Compilation\ModuleLookupContext;

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

	/** @throws CompilationException */
	public function sourceOf(string $moduleName): string {
		foreach($this->sourceRoots as $sourceRoot) {
			$sourcePath = $this->pathOf($sourceRoot, $moduleName);
			if(file_exists($sourcePath) && is_readable($sourcePath)) {
				return file_get_contents($sourcePath);
			}
		}
		throw new CompilationException("Module not found: $moduleName");
	}
}