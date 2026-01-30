<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContextFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethodValidator as UserlandMethodValidatorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

final readonly class UserlandMethodValidator implements UserlandMethodValidatorInterface {

	public function __construct(
		private DependencyContextFactory $dependencyContextFactory,
		private UserlandMethodRegistry $userlandMethodRegistry,
		private ValidationFactory $validationFactory,
	) {}

	public function validateAll(): ValidationResult {
		$validationResult = $this->validationFactory->emptyValidationResult;

		$allMethods = $this->userlandMethodRegistry->allMethods();
		foreach ($allMethods as $methodName => $methods) {
			foreach ($methods as $method) {
				$step = $method->validateFunction();
				if ($step instanceof ValidationFailure) {
					$validationResult = $validationResult->mergeFailure($step);
				}
			}
		}
		return $validationResult;
	}

	public function validateAllDependencies(): DependencyContext {
		$dependencyContext = $this->dependencyContextFactory->dependencyContext;
		$allMethods = $this->userlandMethodRegistry->allMethods();
		foreach($allMethods as $methodName => $methods) {
			foreach($methods as $method) {
				$dependencyContext = $method->validateDependencies($dependencyContext);
			}
		}
		return $dependencyContext;
	}
}