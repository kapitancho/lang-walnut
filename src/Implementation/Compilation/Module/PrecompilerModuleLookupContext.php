<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use Walnut\Lang\Blueprint\Compilation\Module\CodePrecompiler;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Blueprint\Compilation\Module\SourceFinder;

final readonly class PrecompilerModuleLookupContext implements ModuleLookupContext {

	/** @param list<CodePrecompiler> $precompilers */
	public function __construct(
		private SourceFinder $sourceFinder,
		private array $precompilers,
	) {}

	/** @throws ModuleDependencyException */
	public function sourceOf(string $moduleName): string {
		$parsedModuleName = match(true) {
			$moduleName === '$' => 'stdin',
			str_starts_with($moduleName, '$') => 'core/' . substr($moduleName, 1),
			str_starts_with($moduleName, '?') => substr($moduleName, 1) . '-test',
			default => $moduleName,
		};

		foreach ($this->precompilers as $precompiler) {
			$sourceName = $precompiler->determineSourcePath($parsedModuleName);
			if ($sourceName !== null) {

				$sourceCode = $this->sourceFinder->readSource($sourceName);
				if (is_string($sourceCode)) {
					return $precompiler->precompileSourceCode(
						$moduleName,
						$sourceCode
					);
				}
			}
		}
		throw new ModuleDependencyException($moduleName);
	}
}