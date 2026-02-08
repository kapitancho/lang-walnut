<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native\NativeMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodValidator as UserlandMethodValidatorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContextFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
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
		private TypeRegistry $typeRegistry,
		private NativeMethodRegistry $nativeMethodRegistry,
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

		// Check 1: Every custom method should be compatible with all Native methods that it "overrides" for a given type
		foreach ($allMethods as $methodName => $methods) {
			$mn = new MethodName($methodName);
			foreach ($methods as $method) {
				$resolvedTargetType = $this->typeRegistry->typeByName($method->targetType);
				$nativeMethods = $this->nativeMethodRegistry->nativeMethods($resolvedTargetType, $mn);
				$usedMethods = [];
				foreach ($nativeMethods as $nativeMethod) {
					if (array_key_exists($nativeMethod::class, $usedMethods)) {
						continue;
					}
					$usedMethods[$nativeMethod::class] = true;
					$nativeValidation = $nativeMethod->validate($resolvedTargetType, $method->parameterType, $method);
					if ($nativeValidation instanceof ValidationFailure) {
						$validationResult = $validationResult->mergeFailure($nativeValidation);
						continue 2;
					}
					if (!$method->returnType->isSubtypeOf($nativeValidation->type)) {
						$validationResult = $validationResult->mergeFailure(
							$this->validationFactory->error(
								ValidationErrorType::invalidReturnType,
								sprintf(
									"Error in method %s->%s : the method %s is already defined for %s but its return type '%s' is not a subtype of '%s' when called with parameter of type %s",
									$method->targetType,
									$method->methodName,
									$method->methodName,
									$resolvedTargetType,
									$method->returnType,
									$nativeValidation->type,
									$method->parameterType
								),
								$method
							)
						);
						continue 2;
					}
				}
			}
		}

		// Check 2: Every pair of custom methods should have compatible types between each other.
		foreach ($allMethods as $methodName => $methods) {
			$cnt = count($methods);
			$methodTargetTypes = [];
			$methodFnTypes = [];
			foreach ($methods as $method) {
				$methodTargetTypes[] = $this->typeRegistry->typeByName($method->targetType);
				$methodFnTypes[] = $this->typeRegistry->function(
					$method->parameterType,
					$method->returnType
				);
			}
			for ($k = 0; $k < $cnt; $k++) {
				for ($j = 0; $j < $cnt; $j++) {
					if ($k !== $j) {
						if ($methodTargetTypes[$k]->isSubtypeOf($methodTargetTypes[$j]) &&
							!$methodFnTypes[$k]->isSubtypeOf($methodFnTypes[$j])) {
							$validationResult = $validationResult->mergeFailure(
								$this->validationFactory->error(
									ValidationErrorType::userlandMethodMismatch,
									sprintf(
										"Error in method %s->%s : the method %s is already defined for %s and therefore the signature %s should be a subtype of %s",
										$methods[$k]->targetType,
										$methods[$k]->methodName,
										$methodName,
										$methodTargetTypes[$j],
										$methodFnTypes[$k],
										$methodFnTypes[$j]
									),
									$methods[$k]
								)
							);
						}
						if ($k < $j) {
							$iType = $this->typeRegistry->intersection([
								$methodTargetTypes[$k],
								$methodTargetTypes[$j]
							]);
							if (!$iType instanceof NothingType && !$iType instanceof IntersectionType) {
								if (
									!$methodTargetTypes[$k]->isSubtypeOf($methodTargetTypes[$j]) &&
									!$methodTargetTypes[$j]->isSubtypeOf($methodTargetTypes[$k])
								) {
									$validationResult = $validationResult->mergeFailure(
										$this->validationFactory->error(
											ValidationErrorType::userlandMethodMismatch,
											sprintf(
												"Error in method %s->%s : the method %s is defined for both %s and %s but their target types are not compatible with each other",
												$methods[$k]->targetType,
												$methods[$k]->methodName,
												$methodName,
												$methodTargetTypes[$k],
												$methodTargetTypes[$j]
											),
											$methods[$j]
										)
									);
								}
							}
						}
					}
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
