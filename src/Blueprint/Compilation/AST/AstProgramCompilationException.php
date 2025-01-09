<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

use RuntimeException;

final class AstProgramCompilationException extends RuntimeException {
	/** @param AstModuleCompilationException[] $moduleExceptions */
	public function __construct(public array $moduleExceptions) {
		parent::__construct(
			message: sprintf("Compilation errors found in %d module(s):\n %s",
				count($moduleExceptions),
				implode(', ', array_map(
					fn(AstModuleCompilationException $exception) => $exception->getMessage(),
					$moduleExceptions
				))
			)
		);
	}
}