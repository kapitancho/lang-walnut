<?php

namespace Walnut\Lang\Blueprint\Compilation\Module;

interface ModulePathFinder {
	public function pathFor(string $moduleName): string;
}