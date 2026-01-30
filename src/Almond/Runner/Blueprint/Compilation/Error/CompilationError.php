<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

interface CompilationError {
	public CompilationErrorType $errorType { get; }

	public string $errorMessage { get; }

	/** @var list<SourceLocation> */
	public array $sourceLocations { get; }
}