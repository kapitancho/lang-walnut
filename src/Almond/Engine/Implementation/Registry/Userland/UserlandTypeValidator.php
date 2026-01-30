<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeValidator as UserlandTypeValidatorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;
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
			$validationResult = $type->validate($validationResult);
		}
		return $validationResult;
	}
}