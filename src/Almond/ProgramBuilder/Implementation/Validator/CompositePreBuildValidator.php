<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator;

use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class CompositePreBuildValidator implements PreBuildValidator {

	/** @var list<PreBuildValidator> */
	private array $validators;

	public function __construct(PreBuildValidator ... $validators) {
		$this->validators = $validators;
	}

	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;
		foreach ($this->validators as $validator) {
			$result = $result->mergeWith(
				$validator->validate($request)
			);
		}
		return $result;
	}
}