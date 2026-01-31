<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeFinder;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext as ValidationContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory as ValidationFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult as ValidationResultInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope as VariableScopeInterface;
use Walnut\Lang\Almond\Engine\Implementation\Program\VariableScope\VariableScope;

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