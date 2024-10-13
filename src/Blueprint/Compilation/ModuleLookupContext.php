<?php

namespace Walnut\Lang\Blueprint\Compilation;

interface ModuleLookupContext {
	/** @throws CompilationException */
	public function sourceOf(string $moduleName): string;
}