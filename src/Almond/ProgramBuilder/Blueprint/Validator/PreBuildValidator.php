<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator;

interface PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure;
}