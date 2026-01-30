<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContextFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeValidator as UserlandTypeValidatorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

final readonly class UserlandTypeValidator implements UserlandTypeValidatorInterface {

	public function __construct(
		private UserlandTypeRegistry $userlandTypeRegistry,
		private ValidationFactory $validationFactory,
	) {}

	public function validateAll(): ValidationResult {
		$validationResult = $this->validationFactory->emptyValidationResult;

		$allTypes = $this->userlandTypeRegistry->all();
		foreach ($allTypes as $type) {
			$step = $type->validate($validationResult);
			if ($step instanceof ValidationFailure) {
				$validationResult = $validationResult->mergeFailure($step);
			}
		}
		return $validationResult;
	}
}