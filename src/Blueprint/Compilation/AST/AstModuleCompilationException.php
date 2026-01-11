<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

use Walnut\Lang\Blueprint\Compilation\CompilationException;

final class AstModuleCompilationException extends CompilationException {
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