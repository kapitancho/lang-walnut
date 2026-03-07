<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure as PreBuildValidationFailureInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess as PreBuildValidationSuccessInterface;

final readonly class PreBuildValidationSuccess implements PreBuildValidationSuccessInterface {
	public array $errors;
	public function __construct() {
		$this->errors = [];
	}

	/** @param list<SourceNode> $sourceNodes */
	public function withAddedError(
		PreBuildValidationErrorType $errorType,
		string $error,
		array $sourceNodes
	): PreBuildValidationFailure {
		return new PreBuildValidationFailure([
			new PreBuildValidationError($errorType, $error, $sourceNodes)
		]);
	}

	public function mergeWith(PreBuildValidationSuccessInterface|PreBuildValidationFailureInterface $response): PreBuildValidationSuccessInterface|PreBuildValidationFailureInterface {
		return $response;
	}
}