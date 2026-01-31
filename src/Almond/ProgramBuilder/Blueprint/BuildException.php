<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint;

use RuntimeException;
use Throwable;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;

final class BuildException extends RuntimeException {
	public function __construct(
		public Node $node,
		string $message,
		Throwable|null $previous = null
	) {
		parent::__construct($message, previous: $previous);
	}
}