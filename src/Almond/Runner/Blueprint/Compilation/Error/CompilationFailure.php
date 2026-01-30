<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error;

use Walnut\Lang\Almond\Runner\Blueprint\Compilation\CompilationResult;

interface CompilationFailure extends CompilationResult {
	/** @var list<CompilationError> */
	public array $errors { get; }
}