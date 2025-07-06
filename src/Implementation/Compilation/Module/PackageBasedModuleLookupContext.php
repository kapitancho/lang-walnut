<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use Walnut\Lang\Blueprint\Compilation\Module\CodePrecompiler;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Blueprint\Compilation\Module\ModulePathFinder;

final readonly class PackageBasedModuleLookupContext implements ModuleLookupContext {

	/** @param array<string, CodePrecompiler> $precompilers */
	public function __construct(
		private ModulePathFinder $modulePathFinder,
		private array $precompilers,
	) {}

	/** @throws ModuleDependencyException */
	public function sourceOf(string $moduleName): string {
		$sourcePath = $this->modulePathFinder->pathFor($moduleName);
		foreach($this->precompilers as $precompiler) {
			$fullSourcePath = $precompiler->determineSourcePath($sourcePath);
			$sourceCode =
				is_string($fullSourcePath) &&
				file_exists($fullSourcePath) &&
				is_readable($fullSourcePath) ?
					file_get_contents($fullSourcePath) : null;
			if (is_string($sourceCode)) {
				return $precompiler->precompileSourceCode(
					$moduleName,
					$sourceCode
				);
			}
		}
		throw new ModuleDependencyException($moduleName);
	}
}