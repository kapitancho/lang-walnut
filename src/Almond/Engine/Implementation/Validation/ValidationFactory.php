<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeFinder;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext as ValidationContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationError;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory as ValidationFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult as ValidationResultInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScope as VariableScopeInterface;
use Walnut\Lang\Almond\Engine\Implementation\VariableScope\VariableScope;

final class ValidationFactory implements ValidationFactoryInterface {

	public readonly ValidationResultInterface $emptyValidationResult;

	public function __construct(
		private readonly TypeFinder $typeFinder,
	) {
		$this->emptyValidationResult = new ValidationResult([]);
	}

	public ValidationContextInterface $emptyValidationContext {
		get => $this->emptyValidationContext ??= new ValidationContext(
			new VariableScope([]),
			$this->initialExpressionType,
			$this->initialReturnType
		);
	}

	private Type $initialExpressionType {
		get => $this->initialExpressionType ??= $this->typeFinder->typeByName(new TypeName('Null'));
	}

	private Type $initialReturnType {
		get => $this->initialReturnType ??= $this->typeFinder->typeByName(new TypeName('Nothing'));
	}

	public function validationSuccess(Type $type): ValidationSuccess {
		return new ValidationSuccess($type);
	}

	public function fromVariableScope(VariableScopeInterface $variableScope): ValidationContextInterface {
		return new ValidationContext(
			$variableScope,
			$this->initialExpressionType,
			$this->initialReturnType
		);
	}

	public function error(ValidationErrorType $type, string $message, mixed $origin): ValidationFailure {
		return $this->emptyValidationResult->withError($type, $message, $origin);
	}
	public function validationError(ValidationError $error): ValidationFailure {
		return $this->emptyValidationResult->withValidationError($error);
	}
}