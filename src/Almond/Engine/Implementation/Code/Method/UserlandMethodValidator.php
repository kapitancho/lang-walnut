<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodValidator as UserlandMethodValidatorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContextFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory;

final readonly class UserlandMethodValidator implements UserlandMethodValidatorInterface {

	public function __construct(
		private DependencyContextFactory $dependencyContextFactory,
		private UserlandMethodRegistry $userlandMethodRegistry,
		private ValidationFactory $validationFactory,
		private VariableScopeFactory $variableScopeFactory,
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
		$allValidators = $this->userlandMethodRegistry->allValidators();
		foreach ($allValidators as $typeName => $validator) {
			$step = $validator->validateInVariableScope(
				$this->variableScopeFactory->emptyVariableScope
			);
			if ($step instanceof ValidationFailure) {
				$validationResult = $validationResult->mergeFailure($step);
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