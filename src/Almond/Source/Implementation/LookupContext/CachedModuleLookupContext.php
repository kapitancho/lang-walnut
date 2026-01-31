<?php

namespace Walnut\Lang\Almond\Source\Implementation\LookupContext;

use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;

final class CachedModuleLookupContext implements ModuleLookupContext {

	/** @param array<string, string|null> $cache */
	private array $cache = [];
	public function __construct(
		private readonly ModuleLookupContext $moduleLookupContext,
	) {}

	public function sourceOf(string $moduleName): string|null {
		return array_key_exists($moduleName, $this->cache) ? $this->cache[$moduleName] :
			$this->cache[$moduleName] = $this->moduleLookupContext->sourceOf($moduleName);
	}
}