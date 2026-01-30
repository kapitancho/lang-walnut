<?php

namespace Walnut\Lang\Almond\Source\Implementation\LookupContext;

use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;
use Walnut\Lang\Almond\Source\Blueprint\Precomipiler\CodePrecompiler;
use Walnut\Lang\Almond\Source\Blueprint\SourceFinder\SourceFinder;

final readonly class PrecompilerModuleLookupContext implements ModuleLookupContext {

	/** @param list<CodePrecompiler> $precompilers */
	public function __construct(
		private SourceFinder $sourceFinder,
		private array $precompilers,
	) {}

	public function sourceOf(string $moduleName): string|null {
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
		return null;
	}
}