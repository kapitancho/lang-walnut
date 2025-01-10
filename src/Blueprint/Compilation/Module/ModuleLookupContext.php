<?php

namespace Walnut\Lang\Blueprint\Compilation\Module;

interface ModuleLookupContext {
	/** @throws ModuleDependencyException */
	public function sourceOf(string $moduleName): string;
}