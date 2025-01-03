<?php

namespace Walnut\Lang\Blueprint\AST\Compiler;

use RuntimeException;
use Throwable;
use Walnut\Lang\Blueprint\AST\Node\SourceNode;

final class AstCompilationException extends RuntimeException {
	public function __construct(public SourceNode $node, string $message, Throwable|null $previous = null) {
		parent::__construct(
			message: sprintf("[%s] at %s", $message, $this->node->sourceLocation->startPosition),
			previous: $previous
		);
	}

}