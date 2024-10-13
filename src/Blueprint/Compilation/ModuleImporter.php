<?php

namespace Walnut\Lang\Blueprint\Compilation;

interface ModuleImporter {
	public function importModule(string $moduleName): void;
}