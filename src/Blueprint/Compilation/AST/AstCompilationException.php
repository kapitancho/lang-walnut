<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

use Throwable;
use Walnut\Lang\Blueprint\AST\Node\SourceNode;
use Walnut\Lang\Blueprint\Compilation\CompilationException;

final class AstCompilationException extends CompilationException {
	public function __construct(public SourceNode $node, string $message, Throwable|null $previous = null) {
		parent::__construct(
			message: $message,
			previous: $previous
		);
	}

}