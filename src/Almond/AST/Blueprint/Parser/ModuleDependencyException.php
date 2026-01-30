<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Parser;

use RuntimeException;

final class ModuleDependencyException extends RuntimeException {
	/** @param string[] $path */
	public function __construct(
		public readonly string $module,
		public readonly array $path = []
	) {
		$errorMessage = $this->isLoop ?
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

	public bool $isLoop {
		get => in_array($this->module, $this->path, true);
	}
}