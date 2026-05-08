<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodValidator;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeValidator;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramValidator as ProgramValidatorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class ProgramValidator implements ProgramValidatorInterface {

	public function __construct(
		public UserlandTypeValidator $userlandTypeValidator,
		public UserlandMethodValidator $userlandMethodValidator,
	) {}

	public function validateSource(): ValidationResult {
		$validationResult = $this->userlandTypeValidator->validateAll();
		$methodValidationResult = $this->userlandMethodValidator->validateAll();
		if ($methodValidationResult instanceof ValidationFailure) {
			$validationResult = $validationResult->mergeFailure($methodValidationResult);
		}
		return $validationResult;
	}

	public function validateDependencies(): ValidationResult {
		return $this->userlandMethodValidator
			->validateAllDependencies()
			->asValidationResult();
	}
}