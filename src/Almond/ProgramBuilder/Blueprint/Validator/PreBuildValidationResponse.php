<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;

interface PreBuildValidationResponse {
	/** @var list<PreBuildValidationError> */
	public array $errors { get; }
	/** @param list<SourceNode> $sourceNodes */
	public function withAddedError(
		PreBuildValidationErrorType $errorType,
		string $error,
		array $sourceNodes
	): PreBuildValidationFailure;

	public function mergeWith(PreBuildValidationResponse $response): PreBuildValidationResponse;
}