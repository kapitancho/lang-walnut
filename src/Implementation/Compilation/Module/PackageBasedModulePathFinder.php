<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\Module\ModulePathFinder;

final readonly class PackageBasedModulePathFinder implements ModulePathFinder {

	/** @param array<string, string> $packageRoots */
	public function __construct(private string $defaultRoot, private array $packageRoots) {}

	/** @throws ModuleDependencyException */
	public function pathFor(string $moduleName): string {
		if ($moduleName[0] === '$') {
			$moduleName = 'core/' . substr($moduleName, 1);
		} elseif ($moduleName[0] === '?') {
			$moduleName = substr($moduleName, 1) . '-test';
		}
		foreach($this->packageRoots as $packageRoot => $path) {
			if (str_starts_with($moduleName, $packageRoot . '/')) {
				return $path . substr($moduleName, strlen($packageRoot));
			}
		}
		return $this->defaultRoot . '/' . $moduleName;
	}
}