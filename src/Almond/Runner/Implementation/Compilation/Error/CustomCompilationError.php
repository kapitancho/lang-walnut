<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation\Error;

use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorType;

final readonly class CustomCompilationError implements CompilationError {

	public function __construct(
		public CompilationErrorType $errorType,
		public string $errorMessage,
		public array $sourceLocations
	) {}
}