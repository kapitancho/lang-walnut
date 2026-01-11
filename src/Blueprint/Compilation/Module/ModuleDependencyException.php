<?php

namespace Walnut\Lang\Blueprint\Compilation\Module;

use Walnut\Lang\Blueprint\Compilation\CompilationException;

final class ModuleDependencyException extends CompilationException {
	/** @param string[] $path */
	public function __construct(
		public readonly string $module,
		public readonly array $path = []
	) {
		$errorMessage = in_array($this->module, $this->path, true) ?
			"Module dependency loop detected" : "Module not found";
		parent::__construct(
			$path === [] ? sprintf("%s: %s", $errorMessage, $module) :
			sprintf("%s: %s, dependency path: %s -> %s",
				$errorMessage,
				$module,
				implode(' -> ', $path),
				$this->module
			)
		);
	}
}