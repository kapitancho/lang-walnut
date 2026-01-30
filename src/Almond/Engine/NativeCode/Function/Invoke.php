<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Value\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class Invoke implements NativeMethod {

	public function __construct(
		private ValidationFactory $validationFactory,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		/*
		$baseTargetType = $this->toTargetBaseType(
			$targetType,
			$typeRegistry->metaType(MetaTypeValue::Function)
		);
		if (!$baseTargetType) {
			// @codeCoverageIgnoreStart
			throw new AnalyserException(
				sprintf("Invalid target type: %s, expected a function", $targetType));
			// @codeCoverageIgnoreEnd
		}

		$p = $baseTargetType->parameterType;
		$parameterType = $this->adjustParameterType(
			$typeRegistry,
			$p,
			$parameterType,
		);
		*/
		if ($targetType instanceof FunctionType) {
			if ($parameterType->isSubtypeOf($targetType->parameterType)) {
				return $this->validationFactory->validationSuccess($targetType->returnType);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("Invalid parameter type: %s, %s expected (target is %s)",
					$parameterType, $targetType->parameterType, $targetType
				),
				$origin
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("Invalid target type: %s, expected a function", $targetType),
			$origin
		);
		// @codeCoverageIgnoreEnd
	}

	/** @throws ExecutionException */
	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof FunctionValue) {
			/*$parameter = $this->adjustParameterValue(
				$programRegistry->valueRegistry,
				$v->type->parameterType,
				$parameter,
			);*/
			return $target->execute($parameter);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException(
			sprintf("Invalid target value: %s, expected a function", $target->type)
		);
		// @codeCoverageIgnoreEnd
	}
}