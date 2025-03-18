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
		foreach($this->precompilers as $extension => $precompiler) {
			$fullSourcePath = $sourcePath . $extension;
			if (file_exists($fullSourcePath) && is_readable($fullSourcePath)) {
				return $precompiler->precompileSourceCode(
					$moduleName,
					file_get_contents($fullSourcePath)
				);
			}
		}
		throw new ModuleDependencyException($moduleName);
	}
}