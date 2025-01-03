<?php

namespace Walnut\Lang\Blueprint\Compilation;

interface ModuleLookupContext {
	/** @throws ModuleDependencyException */
	public function sourceOf(string $moduleName): string;
}