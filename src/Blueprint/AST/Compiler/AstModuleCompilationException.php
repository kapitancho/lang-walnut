<?php

namespace Walnut\Lang\Blueprint\AST\Compiler;

use RuntimeException;

final class AstModuleCompilationException extends RuntimeException {
	/** @param AstCompilationException[] $compilationExceptions */
	public function __construct(public string $moduleName, public array $compilationExceptions) {
		parent::__construct(
			message: sprintf("%d compilation error(s) in module %s : \n%s",
				count($this->compilationExceptions),
				$this->moduleName,
				implode(", \n", array_map(fn(AstCompilationException $compilationException)
					=> $compilationException->getMessage(), $compilationExceptions))
			),
		);
	}

}