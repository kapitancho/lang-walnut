<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator;

interface PreBuildValidationFailure extends PreBuildValidationResponse {
	/** @var non-empty-list<PreBuildValidationError> */
	public array $errors { get; }

	public function mergeWith(PreBuildValidationSuccess|PreBuildValidationFailure $response): PreBuildValidationFailure;
}