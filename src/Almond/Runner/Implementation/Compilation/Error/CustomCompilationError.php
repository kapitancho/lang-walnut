<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorType;

final readonly class CustomCompilationError implements CompilationError {
	/** @param list<SourceLocation> $sourceLocations */
	public function __construct(
		public CompilationErrorType $errorType,
		public string $errorMessage,
		public array $sourceLocations
	) {}
}