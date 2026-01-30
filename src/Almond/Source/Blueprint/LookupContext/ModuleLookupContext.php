<?php

namespace Walnut\Lang\Almond\Source\Blueprint\LookupContext;

interface ModuleLookupContext {
	public function sourceOf(string $moduleName): string|null;
}