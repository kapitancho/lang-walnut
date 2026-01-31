<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Method;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext as MethodContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodFinder as MethodFinderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeFinder;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

final class MethodContext implements MethodContextInterface {
	public function __construct(
		private readonly TypeFinder $typeFinder,
		private readonly ValidationFactory     $validationFactory,
		private readonly MethodFinderInterface $methodFinder,
	) {}

	private Type $nullType {
		get => $this->nullType ??= $this->typeFinder->typeByName(new TypeName('Null'));
	}

	public function methodForType(Type $targetType, MethodName $methodName): Method|UnknownMethod {
		return $this->methodFinder->methodForType($targetType, $methodName);
	}

	public function methodForValue(Value $target, MethodName $methodName): Method|UnknownMethod {
		return $this->methodFinder->methodForValue($target, $methodName);
	}

	public function validateMethod(
		Type $targetType, MethodName $methodName, Type $parameterType, Expression|null $origin
	): ValidationSuccess|ValidationFailure {
		$method = $this->methodFinder->methodForType($targetType, $methodName);
		if ($method instanceof UnknownMethod) {
			return $this->validationFactory->error(
				ValidationErrorType::undefinedMethod,
				sprintf("Method '%s' is not defined for type '%s'.",
					$methodName, $targetType),
				$origin ?? $this
			);
		}
		return $method->validate($targetType, $parameterType, $origin);
	}

	public function validateCast(
		Type $targetType, TypeName $castTypeName, Expression|null $origin
	): ValidationSuccess|ValidationFailure {
		return $this->validateMethod(
			$targetType,
			new MethodName('as' . $castTypeName->identifier),
			$this->nullType,
			$origin
		);
	}

	/** @throws ExecutionException */
	public function executeMethod(Value $target, MethodName $methodName, Value $parameter): Value {
		$method = $this->methodFinder->methodForValue($target, $methodName);
		if ($method instanceof UnknownMethod) {
			throw new ExecutionException(
				sprintf("Method '%s' is not defined for value '%s'.",
					$methodName, $target)
			);
		}
		return $method->execute($target, $parameter);
	}

	/** @throws ExecutionException */
	public function executeCast(Value $target, TypeName $castTypeName): Value {
		return $this->executeMethod(
			$target,
			new MethodName('as' . $castTypeName->identifier),
			$this->nullType->value
		);
	}
}