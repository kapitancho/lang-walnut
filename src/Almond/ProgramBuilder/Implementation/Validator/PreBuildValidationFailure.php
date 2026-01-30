<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationError as PreBuildValidationErrorInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure as PreBuildValidationFailureInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationResponse;

final readonly class PreBuildValidationFailure implements PreBuildValidationFailureInterface {
	/** @param list<PreBuildValidationErrorInterface> $errors */
	public function __construct(public array $errors) {}

	/** @param list<SourceNode> $sourceNodes */
	public function withAddedError(
		PreBuildValidationErrorType $errorType,
		string $error,
		array $sourceNodes
	): PreBuildValidationFailureInterface {
		return new PreBuildValidationFailure([...$this->errors,
			new PreBuildValidationError($errorType, $error, $sourceNodes)
		]);
	}


	public function mergeWith(PreBuildValidationResponse $response): PreBuildValidationFailure {
		return new self([...$this->errors, ...$response->errors]);
	}

}