<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContextFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionContextFiller;
use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction as UserlandFunctionInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScope;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableValueScope;

final readonly class UserlandFunction implements UserlandFunctionInterface {
	public function __construct(
		private ValidationFactory        $validationFactory,
		private ExecutionContextFactory  $executionContextFactory,

		private FunctionContextFiller    $functionContextFiller,
		private DependencyContainer 	 $dependencyContainer,

		public NameAndType               $target,
		public NameAndType               $parameter,
		public NameAndType               $dependency,
		public Type                      $returnType,
		public FunctionBody              $functionBody
	) {}

	public function validateInVariableScope(VariableScope $variableScope): ValidationSuccess|ValidationFailure {

		$validationContext = $this->functionContextFiller->fillValidationContext(
			$this->validationFactory->fromVariableScope($variableScope),
			$this->target,
			$this->parameter,
			$this->dependency,
		);
		$validationResult = $this->functionBody->validateInContext($validationContext);
		if (
			$validationResult instanceof ValidationSuccess &&
			!$validationResult->type->isSubtypeOf($this->returnType)
		) {
			$validationResult = $validationResult->withError(
				ValidationErrorType::invalidReturnType,
				sprintf(
					"Function body return type '%s' is not compatible with declared return type '%s'.",
					$validationResult->type, $this->returnType
				),
				$this
			);
		}
		$targetTypeValidation = $this->target->type->validate($this->validationFactory->emptyValidationResult);
		$parameterTypeValidation = $this->parameter->type->validate($this->validationFactory->emptyValidationResult);
		$dependencyTypeValidation = $this->dependency->type->validate($this->validationFactory->emptyValidationResult);
		$returnTypeValidation = $this->returnType->validate($this->validationFactory->emptyValidationResult);
		foreach([
			$targetTypeValidation,
			$parameterTypeValidation,
	        $returnTypeValidation,
			$dependencyTypeValidation,
		] as $typeValidation) {
			if ($typeValidation instanceof ValidationFailure) {
				$validationResult = $validationResult->mergeFailure($typeValidation);
			}
		}

		return $validationResult;
	}

	public function validate(Type $targetType, Type $parameterType): ValidationSuccess|ValidationFailure {
		$validationResult = $this->validationFactory->validationSuccess($this->returnType);
		if (!$targetType->isSubtypeOf($this->target->type)) {
			$validationResult = $validationResult->withError(
				ValidationErrorType::invalidTargetType,
				sprintf(
					"Function target type '%s' is not compatible with declared target type '%s'.",
					$targetType,
					$this->target->type
				),
				$this
			);
		}
		if (!$parameterType->isSubtypeOf($this->parameter->type)) {
			$validationResult = $validationResult->withError(
				ValidationErrorType::invalidParameterType,
				sprintf(
					"Function parameter type '%s' is not compatible with declared parameter type '%s'.",
					$parameterType,
					$this->parameter->type
				),
				$this
			);
		}
		return $validationResult;
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->dependency->type
			|> (fn(Type $type) => $dependencyContext->withCheckForType($type, $this))
			|> $this->functionBody->validateDependencies(...);
	}

	public function execute(VariableValueScope $variableValueScope, Value|null $targetValue, Value $parameterValue): Value {
		$returnValue = $this->functionBody->execute(
			$this->functionContextFiller->fillExecutionContext(
				$this->executionContextFactory->fromVariableValueScope(
					$variableValueScope
				),
				$this->target,
				$targetValue,
				$this->parameter,
				$parameterValue,
				$this->dependency,
				$this->dependency->type instanceof NothingType ? null :
					$this->dependencyContainer->valueForType($this->dependency->type)
			)
		);
		if (!$returnValue->type->isSubtypeOf($this->returnType)) {
			throw new ExecutionException(
				sprintf(
					"Function return value type '%s' is not compatible with declared return type '%s'.",
					$returnValue->type,
					$this->returnType
				)
			);
		}
		return $returnValue;
	}
}