<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethodValidator;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramValidator as ProgramValidatorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeValidator;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

final readonly class ProgramValidator implements ProgramValidatorInterface {

	public function __construct(
		public UserlandTypeValidator $userlandTypeValidator,
		public UserlandMethodValidator $userlandMethodValidator,
	) {}

	public function validateProgram(): ValidationResult {
		$validationResult = $this->userlandTypeValidator->validateAll();
		$methodValidationResult = $this->userlandMethodValidator->validateAll();
		if ($methodValidationResult instanceof ValidationFailure) {
			$validationResult = $validationResult->mergeFailure($methodValidationResult);
		}
		if (!$validationResult->hasErrors()) {
			$validationResult = $this->userlandMethodValidator
				->validateAllDependencies()
				->asValidationResult();
		}
		return $validationResult;
	}
}