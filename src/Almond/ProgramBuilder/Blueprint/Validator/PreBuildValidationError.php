<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;

interface PreBuildValidationError {
	public PreBuildValidationErrorType $errorType { get; }
	public string $errorMessage { get; }
	/** @var list<SourceNode> */
	public array $sourceNodes { get; }
}