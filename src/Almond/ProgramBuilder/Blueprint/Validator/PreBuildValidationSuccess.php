<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator;

interface PreBuildValidationSuccess extends PreBuildValidationResponse {
	/** @var array{} */
	public array $errors { get; }
}